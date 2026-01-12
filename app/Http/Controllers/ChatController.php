<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        $users = User::where('id', '!=', Auth::id())->get();
        // Get groups the user belongs to
        $groups = Auth::user()->groups; 
        
        return view('chat.index', compact('users', 'groups'));
    }

    // --- MESSAGING LOGIC ---

    public function fetchMessages($type, $id)
    {
        if ($type === 'user') {
            // Existing 1-on-1 logic
            $messages = Message::where(function($q) use ($id) {
                    $q->where('from_id', Auth::id())->where('to_id', $id);
                })
                ->orWhere(function($q) use ($id) {
                    $q->where('from_id', $id)->where('to_id', Auth::id());
                })
                ->orderBy('created_at', 'asc')->get();
        } else {
            // Group logic
            $group = Group::findOrFail($id);
            // Ensure user is in group
            if(!$group->users->contains(Auth::id())) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            $messages = Message::where('group_id', $id)
                ->with('sender') // Eager load sender name
                ->orderBy('created_at', 'asc')->get();
        }

        return response()->json($messages);
    }

   public function sendMessage(Request $request)
{
    // 1. Setup default data
    $data = [
        'from_id' => Auth::id(),
        'is_read' => false,
        'type' => $request->type ?? 'text', // text, audio, call
    ];

    // 2. Handle Target (User or Group)
    if ($request->context === 'group') {
        $data['group_id'] = $request->target_id;
    } else {
        $data['to_id'] = $request->target_id;
    }

    // 3. Handle Audio File Upload
    if ($request->hasFile('attachment')) {
        $file = $request->file('attachment');
        $path = $file->store('voice_notes', 'public'); // Save to storage/app/public/voice_notes
        $data['attachment'] = $path;
        $data['message'] = 'Voice Note'; // Fallback text
    } 
    // 4. Handle Call Link or Text
    else {
        $data['message'] = $request->message;
    }

    $message = Message::create($data);
    
    // Load sender for immediate UI update
    $message->load('sender');

    return response()->json($message);
}

    // --- GROUP MANAGEMENT LOGIC ---

   public function createGroup(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'users' => 'array', // Ensure it's an array
        'users.*' => 'exists:users,id' // Ensure every ID exists in DB
    ]);

    // 1. Create the Group
    $group = Group::create([
        'name' => $request->name,
        'owner_id' => Auth::id()
    ]);

    // 2. Prepare User IDs
    // Get the IDs selected in the form
    $userIds = $request->input('users', []);
    
    // Force them to be integers (fixes some string/int mismatch issues)
    $userIds = array_map('intval', $userIds);
    
    // Add the CREATOR (You) to the list, otherwise you won't be in your own group
    if (!in_array(Auth::id(), $userIds)) {
        $userIds[] = Auth::id();
    }

    // 3. Attach all IDs to the group in the pivot table
    $group->users()->sync($userIds); // sync() is safer than attach() here

    return redirect()->back()->with('success', 'Group created successfully!');
}
    
    public function deleteMessage($id)
    {
        $message = Message::findOrFail($id);

        // Security: Only sender can delete
        if ($message->from_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $message->delete();
        return response()->json(['status' => 'deleted']);
    }

    // 2. Edit a specific message
    public function updateMessage(Request $request, $id)
    {
        $message = Message::findOrFail($id);

        // Security: Only sender can edit, and only text messages
        if ($message->from_id !== Auth::id() || $message->type !== 'text') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate(['message' => 'required|string']);

        $message->update(['message' => $request->message]);
        return response()->json($message);
    }

    // 3. Delete a Group (Owner or Admin)
    public function deleteGroup($id)
    {
        $group = Group::findOrFail($id);
        $user = Auth::user();

        // Check if user is Owner OR has 'admin' role
        // (Assuming you have a hasRole method or similar logic)
        $isAdmin = method_exists($user, 'hasRole') ? $user->hasRole('admin') : false;

        if ($group->owner_id !== $user->id && !$isAdmin) {
            abort(403, 'Unauthorized action.');
        }

        $group->delete(); // Cascading delete will remove users/messages due to migration
        
        return redirect()->route('chat.index')->with('success', 'Group deleted successfully');
    }
}