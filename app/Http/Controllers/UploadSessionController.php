<?php

namespace App\Http\Controllers;

use App\Models\UploadSession;
use Illuminate\Http\Request;
use Symfony\Component\Uid\Uuid;

class UploadSessionController extends Controller
{
    public function newSessionView(Request $request)
    {
        return view('sessions/view', ['uploadSession' => $this->newSession($request)]);
    }

    public function newSessionApi(Request $request)
    {
        $session = $this->newSession($request);
        return response()->json([
            'id' => $session->id,
            'token' => $session->token,
        ]);
    }

    public function newSession(Request $request)
    {
        // Validate the request parameters
        $request->validate([
            'expires' => 'sometimes|integer|in:-1,0,3600,86400,604800,2592000',
            'upload_expires' => 'sometimes|integer|in:-1,0,3600,86400,604800,2592000',
        ]);

        $session = new UploadSession([
            'token' => Uuid::v4()->toString(),
            'expires' => $request->input('expires', UploadSession::EXPIRES_BURN),
            'upload_expires' => $request->input('upload_expires', UploadSession::EXPIRES_BURN),
        ]);
        $session->save();

        return $session;
    }

    public function deleteSession(Request $request, $id)
    {
        $session = UploadSession::find($id);
        if (!$session) {
            return redirect('sessions')->with('error', 'Session not found.');
        }

        $session->delete();
        return redirect('sessions')->with('success', 'Session deleted successfully.');
    }

    public function index(Request $request)
    {
        $sessions = UploadSession::all();
        return view('sessions/index', ['uploadSessions' => $sessions]);
    }

    public function viewSession(Request $request, $id)
    {
        $session = UploadSession::find($id);
        if (!$session) {
            return redirect('sessions')->with('error', 'Session not found.');
        }
        return view('sessions/view', ['uploadSession' => $session]);
    }
}
