<?php

namespace App\Http\Controllers;

use App\Models\PlaylistVideo;
use App\Models\videoupload;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class VideoUploader extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function CreatePlaylist(Request $request)
    {
        $request->validate([
            'PlaylistTitle' => 'required',
            'PlaylistDescription' => 'required',
        ]);
        $currentDateTime = Carbon::now();
            $date = $currentDateTime->format('Y-m-d');

        $user = $request->user();

        PlaylistVideo::create([
            'UsersID' => $user->id,
            'PlaylistTitle' => $request->input('PlaylistTitle'),
            'PlaylistDescription' => $request->input('PlaylistDescription'),
            'PlaylistRank' => $request->input('PlaylistRank'),
            'playlistCategory' => $request->input('playlistCategory') ?? null,
            'Date' => $date
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function Store(Request $request)
    {
        $request->validate([
            'video' => 'required|file|max:2097152', // 2 GB in kilobytes (1024 * 1024 * 2)
            'VideoCategory' => 'required|string|max:255',
            'VideoTitle' => 'required|string|max:255',
            'VideoDescription' => 'nullable|string',
            'VideoRank' => 'nullable|integer', // Ensure VideoRank is an integer
            'VideoPlaylistID' => 'nullable|integer',
        ]);

        $user = $request->user();
        

        if ($request->file('video')) {
            $path = $request->file('video')->store('videos', 'public');
            if($path){
            $currentDateTime = Carbon::now();
            $date = $currentDateTime->format('Y-m-d');
                        // Prepare data for insertion
                        $videoData = [
                            'UsersID' => $request->user()->id,
                            'VideoName' => $path,
                            'VideoCategory' => $request->input('VideoCategory'),
                            'VideoTitle' => $request->input('VideoTitle'),
                            'VideoDescription' => $request->input('VideoDescription') ?? '',
                            'Date' => $date, // Set Date
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
            
                        // Only include these fields if they are provided and valid
                        if ($request->filled('VideoRank')) {
                            $videoData['VideoRank'] = $request->input('VideoRank');
                        } else {
                            $videoData['VideoRank'] = null;
                        }
            
                        if ($request->filled('VideoPlaylistID')) {
                            $videoData['VideoPlaylistID'] = $request->input('VideoPlaylistID');
                        } else {
                            $videoData['VideoPlaylistID'] = null;
                        }
                        
                        $videoupload = videoupload::create($videoData);
            }

            return response()->json(['path' => $path], 201);
        }

        return response()->json(['error' => 'File not uploaded'], 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function Destroy(Request $request)
    {
        $ID = $request->query('ID');

        $videoupload = videoupload::find($ID);

        $videoPath = $videoupload->VideoName;

        $fullVideoPath = $videoPath;

        \Log::info('Attempting to delete file: ' . $fullVideoPath);

        if (Storage::disk('public')->exists($fullVideoPath)) {

            Storage::disk('public')->delete($fullVideoPath);

            videoupload::destroy($ID);

            return response()->json(['message' => 'Video deleted successfully.']);
        } else {
            // Handle the case where the file does not exist
            return response()->json(['message' => 'File not found.'], 404);
        }
    }
}
