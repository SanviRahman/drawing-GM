<?php
namespace App\Services;
use App\Models\Media;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class VideoMediaService
{
    public function syncFromRequest(Request $request, Video $video): void
    {
        $this->syncVideoFile($request,$video);
        $this->syncPoster($request,$video);
    }
    public function purgeAll(Video $video): void
    {
        Media::withTrashed()->where('model_type',$video->getMorphClass())->where('model_id',$video->getKey())->whereIn('collection_name',[Video::VIDEO_COLLECTION,Video::POSTER_COLLECTION])->get()->each(fn(Media $media)=>$media->forceDelete());
    }
    private function syncVideoFile(Request $request, Video $video): void
    {
        if ($request->hasFile('video_file')) {
            $this->clearCollection($video,Video::VIDEO_COLLECTION);
            $file=$request->file('video_file');
            $video->addMedia($file)->usingFileName($this->safeFilename($file->getClientOriginalExtension(),'mp4'))->toMediaCollection(Video::VIDEO_COLLECTION,'public');
            return;
        }
        if ($request->filled('video_file_media_id')) {
            $media=Media::query()->find((int)$request->input('video_file_media_id'));
            if (! $media||! $media->isPickerSafe()) throw ValidationException::withMessages(['video_file_media_id'=>'The selected video is unavailable or cannot be reused.']);
            if (! in_array((string)$media->mime_type,['video/mp4','video/webm'],true)) throw ValidationException::withMessages(['video_file_media_id'=>'The selected media must be an MP4 or WebM video.']);
            $this->clearCollection($video,Video::VIDEO_COLLECTION);
            $media->copy($video,Video::VIDEO_COLLECTION,'public');
            return;
        }
        if ($request->boolean('video_file_remove')) $this->clearCollection($video,Video::VIDEO_COLLECTION);
    }
    private function syncPoster(Request $request, Video $video): void
    {
        if ($request->hasFile('video_poster')) {
            $this->clearCollection($video,Video::POSTER_COLLECTION);
            $file=$request->file('video_poster');
            $video->addMedia($file)->usingFileName($this->safeFilename($file->getClientOriginalExtension(),'jpg'))->toMediaCollection(Video::POSTER_COLLECTION,'public');
            return;
        }
        if ($request->filled('video_poster_media_id')) {
            $media=Media::query()->find((int)$request->input('video_poster_media_id'));
            if (! $media||! $media->isPickerSafe()) throw ValidationException::withMessages(['video_poster_media_id'=>'The selected poster is unavailable or cannot be reused.']);
            if (! $media->isImage()) throw ValidationException::withMessages(['video_poster_media_id'=>'The selected poster must be an image.']);
            $this->clearCollection($video,Video::POSTER_COLLECTION);
            $media->copy($video,Video::POSTER_COLLECTION,'public');
            return;
        }
        if ($request->boolean('video_poster_remove')) $this->clearCollection($video,Video::POSTER_COLLECTION);
    }
    private function clearCollection(Video $video,string $collection): void
    {
        if ($video->hasMedia($collection)) $video->clearMediaCollection($collection);
    }
    private function safeFilename(?string $extension,string $fallback): string
    {
        $extension=strtolower(trim((string)$extension));
        $extension=preg_replace('/[^a-z0-9]+/','',$extension)?:$fallback;
        return Str::uuid().'.'.$extension;
    }
}
