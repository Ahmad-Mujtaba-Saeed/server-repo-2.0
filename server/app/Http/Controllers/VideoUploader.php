<?php

namespace App\Http\Controllers;

use App\Models\images;
use App\Models\PlaylistVideo;
use App\Models\videoupload;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Response;

class VideoUploader extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function PlaylistData()
    {

        $playlistData = PlaylistVideo::all();
        if($playlistData){
            $response = [
                'success' => true,
                'data' => $playlistData
            ];
        }
        else{
            $response = [
                'success' => false,
                'message' => "Failed to fetch playlist data"
            ];
        }
        return Response()->json($response);
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

        $playlistVideo = PlaylistVideo::create([
            'UsersID' => $user->id,
            'PlaylistTitle' => $request->input('PlaylistTitle'),
            'PlaylistDescription' => $request->input('PlaylistDescription'),
            'PlaylistRank' => $request->input('PlaylistRank') ?? null,
            'PlaylistCategory' => $request->input('playlistCategory') ?? null,
            'Date' => $date
        ]);

        if($playlistVideo){
            $response = [
                'success' => true,
                'message' => "Successfully created new playlist"
            ];
        }else{
            $response = [
                'success' => false,
                'message' => "Failed to created playlist"
            ];
        }
        return Response()->json($response);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function GetplaylistData(Request $request){
        $PlaylistID = $request->query('PlaylistID');

        $playlistData = PlaylistVideo::find($PlaylistID)->with('videos.images')->get();

        if ($playlistData) {
            foreach ($playlistData->videos as $Video ){
                if (isset($Video->images)) {
                    $imgPath = $Video->images->ImageName;
                    $Imgdata = base64_encode(file_get_contents(public_path($imgPath)));
                    $Video->images->setAttribute('data', $Imgdata);
                }
            }
        return response()->json(['success' => true, 'message' => 'playlist', 'data' => $playlistData]);
        } else {
            return response()->json(['success' => false, 'data' => [] ,'message' => 'Playlist Not found']);
        }
    }


    public function UploadComment(Request $request){
        
    }


    public function ShowVideoPicWData(Request $request){
        $user = $request->user();
        if($user->role == 'Admin'){
            $validatedData = $request->validate([
                'ClassRank' => 'required|integer',
                'Subject' => 'required|string|max:255',
            ]);
            if($validatedData['Subject'] == "General"){
                $videos = videoupload::where('VideoPlaylistID',null)
                ->with(['users.images','images'])
                ->get();
                if ($videos) {
                    foreach ($videos as $Eachvideo) {
                            $imgPath = $Eachvideo->images->ImageName;
                            $Imgdata = base64_encode(file_get_contents(public_path($imgPath)));
                            $Eachvideo->images->setAttribute('data', $Imgdata);
                    }
                    return response()->json(['success' => true, 'message' => 'video', 'data' => $videos]);
                } else {
                    return response()->json(['success' => false, 'data' => [] ,'message' => 'Video Not found']);
            }
        }
        else{
            // Fetch the data using the validated query parameters
            $data = PlaylistVideo::where('PlaylistRank', $validatedData['ClassRank'])
                ->where('PlaylistCategory', $validatedData['Subject'])
                ->with(['users.images','videos.images'])
                ->get();

                if ($data) {
                    foreach ($data as $EachPlaylist) {
                        if(isset($EachPlaylist->users->images)){
                            $imgPath = $EachPlaylist->users->images->ImageName;
                            $Imgdata = base64_encode(file_get_contents(public_path($imgPath)));
                            $EachPlaylist->users->images->setAttribute('data', $Imgdata);
                        }
                        if (isset($EachPlaylist->videos[0]->images)) {
                            $imgPath = $EachPlaylist->videos[0]->images->ImageName;
                            $Imgdata = base64_encode(file_get_contents(public_path($imgPath)));
                            $EachPlaylist->videos[0]->images->setAttribute('data', $Imgdata);
                        }
                    }
                return response()->json(['success' => true, 'message' => 'playlist', 'data' => $data]);
                } else {
                    return response()->json(['success' => false, 'data' => [] ,'message' => 'Playlist Not found']);
                }
        }
    }

    }


    public function Store(Request $request)
    {
        $request->validate([
            'video' => 'required|file|max:2097152', // 2 GB in kilobytes (1024 * 1024 * 2)
            'VideoTitle' => 'required|string|max:255',
            'VideoDescription' => 'nullable|string',
            'VideoPlaylistID' => 'nullable|integer',
        ]);

        $user = $request->user();

        $pic = $request->input('thumbnail');
        if (isset($pic)) {
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $pic));
            if ($imageData === false) {
                throw new \Exception('Failed to decode image data');
            }

            $extension = image_type_to_extension(exif_imagetype($pic));
            $filename = uniqid() . $extension;
            $storagePath = 'images/';
            $savePath = public_path($storagePath . $filename);

            if (file_put_contents($savePath, $imageData) === false) {
                throw new \Exception('Failed to save image file');
            }

            $image = new images();
            $image->ImageName = $storagePath . $filename;
            $image->save();
        }
        if(!$image){
            throw new \Exception('Failed to save image Name to Database');
        }
        if ($request->file('video')) {
            $path = $request->file('video')->store('videos', 'public');
            if($path){
            $currentDateTime = Carbon::now();
            $date = $currentDateTime->format('Y-m-d');
                        // Prepare data for insertion
                        $videoData = [
                            'UsersID' => $request->user()->id,
                            'VideoName' => $path,
                            'VideoLength' => $request->input('VideoLength'),
                            'UploadedImgID' => $image->id,
                            'VideoTitle' => $request->input('VideoTitle'),
                            'VideoDescription' => $request->input('VideoDescription') ?? '',
                            'Date' => $date, // Set Date
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
            
                        if ($request->filled('VideoPlaylistID')) {
                            $videoData['VideoPlaylistID'] = $request->input('VideoPlaylistID');
                        } else {
                            $videoData['VideoPlaylistID'] = null;
                        }

                        $videoupload = videoupload::create($videoData);
            }

            return response()->json(['success'=> true ,'message' => 'successfully uploaded video']);
        }

        return response()->json(['success'=> false ,'message' => 'Failed to upload video']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function Show(Request $request)
    {
        $id = $request->query('ID');
        $uploadedVideo = videoupload::with(['users:id,name,email','playlists'])->find($id);
    
        if (!$uploadedVideo) {
            return response()->json(['success' => false ,'message' => 'Video not found.']);
        }
    
        $path = storage_path('app/public/' . $uploadedVideo->VideoName);
    
        if (!file_exists($path)) {
            return response()->json(['success' => false , 'message' => 'Video not found.']);
        }
    
        $fileContents = file_get_contents($path);
        $encodedFile = base64_encode($fileContents);
    
        return response()->json([
            'success' => true,
            'data' => $uploadedVideo,
            'file' => $encodedFile,
        ]);
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
