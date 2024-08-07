<?php

namespace App\Http\Controllers;

use App\Models\comments;
use App\Models\images;
use App\Models\PlaylistVideo;
use App\Models\users;
use App\Models\videoupload;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Response;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Stream;



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
            'PlaylistTitle' => 'required|max:100',
            'PlaylistDescription' => 'required|max:1000',
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

        $playlistData = PlaylistVideo::where('id',$PlaylistID)->with('videos.images')->first();

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
        if($user->role == 'Admin' || $user->role == 'Teacher'){
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
        $data = PlaylistVideo::where('PlaylistRank', $validatedData['ClassRank'])
                ->where('PlaylistCategory', $validatedData['Subject'])
                ->with(['users.images','videos.images'])
                ->get();

                if ($data) {
                    foreach ($data as $EachPlaylist) {
                        if(isset($EachPlaylist->videos[0])){
                        if(isset($EachPlaylist->users->images->ImageName)){
                            $imgPath = $EachPlaylist->users->images->ImageName;
                            $Imgdata = base64_encode(file_get_contents(public_path($imgPath)));
                            $EachPlaylist->users->images->setAttribute('data', $Imgdata);
                        }
                        if (isset($EachPlaylist->videos[0]->images->ImageName)) {
                            $imgPath = $EachPlaylist->videos[0]->images->ImageName;
                            $Imgdata = base64_encode(file_get_contents(public_path($imgPath)));
                            $EachPlaylist->videos[0]->images->setAttribute('data', $Imgdata);
                        }
                        }
                    }
                return response()->json(['success' => true, 'message' => 'playlist', 'data' => $data]);
                } else {
                    return response()->json(['success' => false, 'data' => [] ,'message' => 'Playlist Not found']);
                }
    }
        else{
            $validatedData = $request->validate([
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
            $user = users::with('students.classes')->find($user->id);
            $data = PlaylistVideo::where('PlaylistRank', $user->students[0]->classes->ClassRank)
                ->where('PlaylistCategory', $validatedData['Subject'])
                ->with(['users.images','videos.images'])
                ->get();

                if ($data) {
                    foreach ($data as $EachPlaylist) {
                        if(isset($EachPlaylist->videos[0])){
                        if(isset($EachPlaylist->users->images->ImageName)){
                            $imgPath = $EachPlaylist->users->images->ImageName;
                            $Imgdata = base64_encode(file_get_contents(public_path($imgPath)));
                            $EachPlaylist->users->images->setAttribute('data', $Imgdata);
                        }
                        if (isset($EachPlaylist->videos[0]->images->ImageName)) {
                            $imgPath = $EachPlaylist->videos[0]->images->ImageName;
                            $Imgdata = base64_encode(file_get_contents(public_path($imgPath)));
                            $EachPlaylist->videos[0]->images->setAttribute('data', $Imgdata);
                        }
                        }
                    }
                return response()->json(['success' => true, 'message' => 'playlist', 'data' => $data]);
                } else {
                    return response()->json(['success' => false, 'data' => [] ,'message' => 'Playlist Not found']);
                }
    }

    }


    public function Store(Request $request)
    {
        $request->validate([
            'video' => 'required|file|max:2097152', // 2 GB in kilobytes (1024 * 1024 * 2)
            'VideoTitle' => 'required|string|max:100',
            'VideoDescription' => 'nullable|string|max:10000',
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
                if(!$videoupload){
                $PrevImage = images::find($image->id)->first();
                $PrevImagePath = $PrevImage->ImageName;

                $fullImagePath = public_path($PrevImagePath);
            
                if (file_exists($fullImagePath)) {
                    if (unlink($fullImagePath)) {
                        \Log::info('Image file deleted successfully: ' . $fullImagePath);
                        $PrevImage->delete();
                    }
                }
                        }
            }

            return response()->json(['success'=> true ,'message' => 'successfully uploaded video']);
        }

        return response()->json(['success'=> false ,'message' => 'Failed to upload video']);
    }

    public function StoreComment(Request $request){
        $user = $request->user();
        $ID = $user->id;
        $VideoID = (int) $request->input('VideoID');
        $Comment = $request->input('Comment');
        $commentstored = comments::create([
            'UsersID' => $ID,
            'Comment' => $Comment,
            'VideoID' => $VideoID
        ]);
        if($commentstored){
            return response()->json(['success'=> true ,'message' => 'successfully uploaded comment']);
        }else{
            return response()->json(['success'=> false ,'message' => 'Failed to upload comment']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function ShowInfo(Request $request)
    {
        $id = $request->query('ID');
        $uploadedVideo = videoupload::with(['users:id,name,email','playlists','comments.users.images'])->find($id);
        
        if ($uploadedVideo) {
            $comments = $uploadedVideo->comments;
            foreach ($comments as $comment) {
                if (isset($comment->users->images[0])) {
                    $imgPath = $comment->users->images[0]->ImageName;
                    $data = base64_encode(file_get_contents(public_path($imgPath)));
                    $comment->users->images[0]->setAttribute('data', $data);
                }
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Video not found']);
        }

        if (!$uploadedVideo) {
            return response()->json(['success' => false ,'message' => 'Video not found.']);
        }
    
        $path = storage_path('app/public/' . $uploadedVideo->VideoName);
    
        if (!file_exists($path)) {
            return response()->json(['success' => false , 'message' => 'Video not found.']);
        }
        return response()->json([
            'success' => true,
            'data' => $uploadedVideo,
        ]);
    }
    
    public function Show(Request $request) {
        $id = $request->query('ID');
        $uploadedVideo = videoupload::find($id);
    
        if (!$uploadedVideo) {
            abort(404, 'Video not found');
        }
    
        $filePath = Storage::disk('public')->path($uploadedVideo->VideoName);
    
        if (!Storage::disk('public')->exists($uploadedVideo->VideoName)) {
            abort(404, 'File not found');
        }
    
        $fileSize = Storage::disk('public')->size($uploadedVideo->VideoName);
        $stream = Storage::disk('public')->readStream($uploadedVideo->VideoName);
        
        $rangeHeader = $request->header('Range');
        if ($rangeHeader) {
            // Parse range header (e.g., "bytes=0-100")
            preg_match('/bytes=(\d+)-(\d+)?/', $rangeHeader, $matches);
            $start = (int) $matches[1];
            $end = isset($matches[2]) ? (int) $matches[2] : $fileSize - 1;
    
            // Validate range
            $start = max(0, $start);
            $end = min($end, $fileSize - 1);
    
            // Set headers for partial content response
            $length = $end - $start + 1;
            $headers = [
                'Content-Type' => Storage::disk('public')->mimeType($uploadedVideo->VideoName),
                'Content-Length' => $length,
                'Accept-Ranges' => 'bytes',
                'Content-Range' => "bytes $start-$end/$fileSize",
            ];
    
            return response()->stream(function () use ($stream, $start, $length) {
                fseek($stream, $start);
                echo fread($stream, $length);
            }, 206, $headers);
        }
    
        // If Range header is not present, stream the entire file
        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => Storage::disk('public')->mimeType($uploadedVideo->VideoName),
            'Content-Length' => $fileSize,
            'Accept-Ranges' => 'bytes',
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

        $ImgID = $videoupload->UploadedImgID;

        $image = images::find($ImgID);

        \Log::info('Attempting to delete file: ' . $fullVideoPath);

        if (Storage::disk('public')->exists($fullVideoPath)) {

            $imagePath = $image->ImageName;
                $fullImagePath = public_path($imagePath);

                if (file_exists($fullImagePath)) {
                    // Delete the image file
                    if (unlink($fullImagePath)) {
                        \Log::info('Image file deleted successfully: ' . $fullImagePath);
                        $image->delete();
                    }
                }

            Storage::disk('public')->delete($fullVideoPath);

            videoupload::destroy($ID);

            return response()->json(['success' => true,  'message' => 'Video deleted successfully.']);
        } else {
            // Handle the case where the file does not exist
            return response()->json(['success'=> false, 'message' => 'File not found.'], 404);
        }
    }

    public function destroyPlaylist(Request $request)
    {
        $ID = $request->query('ID');

        $PlaylistVideo = PlaylistVideo::with('videos')->find($ID);

        $videos = $PlaylistVideo->videos;

        foreach ($videos as $video){
            $videoPath = $video->VideoName;
        $fullVideoPath = $videoPath;
        $videoID = $video->id;
        $ImgID = $video->UploadedImgID;
        $image = images::find($ImgID);

        \Log::info('Attempting to delete file: ' . $fullVideoPath);

        if (Storage::disk('public')->exists($fullVideoPath)) {
            $imagePath = $image->ImageName;
            $fullImagePath = public_path($imagePath);

            if (file_exists($fullImagePath)) {
                // Delete the image file
                if (unlink($fullImagePath)) {
                    \Log::info('Image file deleted successfully: ' . $fullImagePath);
                    $image->delete();
                }
            }
            Storage::disk('public')->delete($fullVideoPath);
            videoupload::destroy($videoID);
            \Log::info('deleted file: ' . $fullVideoPath);
        }
        }
        $playlist = PlaylistVideo::destroy($ID);
        if($playlist){
            return ReturnData(true,'','Successfully deleted Playlist');
        }else{
            return ReturnData(true,'','Failed to delete Playlist');
        }
    }
}
