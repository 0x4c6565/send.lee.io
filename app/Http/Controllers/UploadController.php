<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use App\Models\UploadSession;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    const BLOCK_SIZE = 16;

    public function getUploadApi(Request $request, $id)
    {
        $upload = Upload::where('short_id', $id)->first();
        if ($upload === null) {
            return response()->json([
                'error' => 'File not found'
            ], 404);
        }

        return response()->json([
            'id' => $upload->short_id,
            'file_name' => $upload->file_name,
            'size' => $upload->size,
        ]);
    }

    public function upload(Request $request)
    {
        $session = UploadSession::where('token', $request->header('X-UPLOAD-SESSION-ID'))->first();
        if (!$session) {
            return response()->json([
                'error' => 'Session not found'
            ], 404);
        }

        if ($session->shouldBurn()) {
            $session->delete();
        }

        $upload = new Upload([
            'file_name' => trim($request->path(), '/'),
            'status' => Upload::STATUS_UPLOADING,
            'expires' => $session->upload_expires,
        ]);

        $upload->save();

        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(config('upload.cipher')));

        $uploadPath = $upload->getUploadFilePath();
        $encryptionKey = $request->query('encryptionKey', config('upload.default_encryption_key'));

        try {
            $outputHandle = fopen($uploadPath, 'wb');
            // Write IV to the beginning of the file
            fwrite($outputHandle, $iv);

            $inputHandle = fopen("php://input", "r");
            $buffer = '';
            $totalBytesRead = 0;

            while (($data = fread($inputHandle, config('upload.chunk_size'))) !== false && $data !== '') {
                $buffer .= $data;
                $totalBytesRead += strlen($data);

                // Process complete blocks
                while (strlen($buffer) >= self::BLOCK_SIZE) {
                    $block = substr($buffer, 0, self::BLOCK_SIZE);
                    $buffer = substr($buffer, self::BLOCK_SIZE);

                    $encryptedBlock = openssl_encrypt($block, config('upload.cipher'), $encryptionKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);

                    if ($encryptedBlock === false) {
                        throw new \Exception('Encryption failed for block');
                    }

                    fwrite($outputHandle, $encryptedBlock);

                    // Update IV for CBC chaining (use the encrypted block as next IV)
                    $iv = $encryptedBlock;
                }
            }

            // Handle remaining data with padding
            if (strlen($buffer) > 0) {
                // Pad the final block using PKCS7 padding
                $padLength = self::BLOCK_SIZE - (strlen($buffer) % self::BLOCK_SIZE);
                $buffer .= str_repeat(chr($padLength), $padLength);

                $encryptedBlock = openssl_encrypt($buffer, config('upload.cipher'), $encryptionKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);

                if ($encryptedBlock === false) {
                    throw new \Exception('Encryption failed for final block');
                }

                fwrite($outputHandle, $encryptedBlock);
            }

            $upload->size = $totalBytesRead;
            $upload->status = Upload::STATUS_COMPLETED;
            $upload->save();

            $downloadPath = $upload->short_id;
            if ($request->has('encryptionKey')) {
                $downloadPath .= '?encryptionKey=' . urlencode($encryptionKey);
            }

            $directDownloadUrl = config('app.url') . '/download/' . $downloadPath;
            $downloadUrl = config('app.url') . '/d/' . $downloadPath;

            if ($request->has('json')) {
                return response()->json([
                    'id' => $upload->short_id,
                    'downloadUrl' => $downloadUrl,
                    'directDownloadUrl' => $directDownloadUrl,
                ]);
            }

            return response($directDownloadUrl . "\n");
        } catch (\Exception $e) {
            if (isset($uploadPath) && file_exists($uploadPath)) {
                unlink($uploadPath);
            }

            $upload->status = Upload::STATUS_FAILED;
            $upload->save();

            return response()->json([
                'error' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        } finally {
            if (isset($outputHandle) && is_resource($outputHandle)) {
                fclose($outputHandle);
            }
            if (isset($inputHandle) && is_resource($inputHandle)) {
                fclose($inputHandle);
            }
        }
    }

    public function downloadForm(Request $request, string $id)
    {
        $upload = Upload::where('short_id', $id)->first();
        if ($upload === null) {
            return abort(404, 'File not found');
        }

        return view('download', ['upload' => $upload]);
    }

    public function download(Request $request, string $id)
    {
        $upload = Upload::where('short_id', $id)->first();
        if ($upload === null) {
            return response()->json([
                'error' => 'File not found'
            ], 404);
        }

        $uploadPath = $upload->getUploadFilePath();
        if (!file_exists($uploadPath)) {
            return response()->json([
                'error' => 'File not found on disk'
            ], 404);
        }
        $encryptionKey = $request->query('encryptionKey', config('upload.default_encryption_key'));

        try {
            $inputHandle = fopen($uploadPath, 'rb');

            // Read IV from the beginning of file
            $ivLength = openssl_cipher_iv_length(config('upload.cipher'));
            $iv = fread($inputHandle, $ivLength);

            if (strlen($iv) !== $ivLength) {
                throw new \Exception('Invalid encrypted file format');
            }

            header("Content-Length: " . $upload->size);
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment; filename=\"" . $upload->file_name . "\"");

            // Disable output buffering for streaming output
            if (ob_get_level()) {
                ob_end_clean();
            }

            $encryptedFileSize = filesize($uploadPath) - $ivLength;
            $bytesRead = 0;
            $previousBlock = null;
            $totalOutputBytes = 0;

            while ($bytesRead < $encryptedFileSize) {
                $encryptedBlock = fread($inputHandle, self::BLOCK_SIZE);
                if ($encryptedBlock === false || strlen($encryptedBlock) === 0) {
                    break;
                }

                $decryptedBlock = openssl_decrypt($encryptedBlock, config('upload.cipher'), $encryptionKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);

                if ($decryptedBlock === false) {
                    throw new \Exception('Decryption failed for block');
                }

                if ($previousBlock !== null) {
                    echo $previousBlock;
                    $totalOutputBytes += strlen($previousBlock);

                    // Flush output
                    if ($totalOutputBytes % (config('upload.chunk_size')) === 0) {
                        if (ob_get_level()) {
                            ob_flush();
                        }
                        flush();
                    }
                }

                // Store current block
                $previousBlock = $decryptedBlock;

                // Update IV for next block
                $iv = $encryptedBlock;
                $bytesRead += strlen($encryptedBlock);
            }

            // Last block - remove PKCS7 padding
            if ($previousBlock !== null) {
                $lastByte = ord(substr($previousBlock, -1));
                if ($lastByte > 0 && $lastByte <= self::BLOCK_SIZE) {
                    // Verify padding is correct
                    $padding = substr($previousBlock, -$lastByte);
                    if ($padding === str_repeat(chr($lastByte), $lastByte)) {
                        $previousBlock = substr($previousBlock, 0, -$lastByte);
                    }
                }

                // Output final block and flush
                echo $previousBlock;
                if (ob_get_level()) {
                    ob_flush();
                }
                flush();
            }
        } catch (\Exception $e) {
            if (ob_get_level()) {
                ob_end_clean();
            }
            return response()->json([
                'error' => 'Decryption failed: ' . $e->getMessage()
            ], 500);
        } finally {
            if (isset($inputHandle) && is_resource($inputHandle)) {
                fclose($inputHandle);
            }
        }

        if ($upload->shouldBurn()) {
            // Delete the file after download if it should burn
            unlink($uploadPath);
            $upload->delete();
        }
    }
}
