<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Expense;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentController extends Controller
{
    private array $allowedMimes = [
        'image/jpeg', 'image/png', 'image/webp', 'image/gif',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/csv', 'text/plain',
    ];

    private array $modelMap = [
        'projects' => Project::class,
        'tasks'    => Task::class,
        'expenses' => Expense::class,
    ];

    public function store(Request $request, string $modelType, int $modelId)
    {
        if (!isset($this->modelMap[$modelType])) {
            abort(404);
        }

        $model = $this->modelMap[$modelType]::findOrFail($modelId);

        $request->validate([
            'file'   => [
                'required',
                'file',
                'max:10240', // 10MB
                function ($attr, $value, $fail) {
                    if (!in_array($value->getMimeType(), $this->allowedMimes)) {
                        $fail('File type not allowed.');
                    }
                },
            ],
        ]);

        $file      = $request->file('file');
        $filename  = $file->getClientOriginalName();
        $safeName  = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path      = $file->storeAs("attachments/{$modelType}/{$modelId}", $safeName, 'public');

        $model->attachments()->create([
            'uploaded_by' => auth()->id(),
            'filename'    => $filename,
            'path'        => $path,
            'mime_type'   => $file->getMimeType(),
            'size'        => $file->getSize(),
        ]);

        return back()->with('success', 'File uploaded successfully.');
    }

    public function destroy(Attachment $attachment)
    {
        // Only uploader or admin can delete
        if (
            auth()->id() !== $attachment->uploaded_by &&
            !auth()->user()->hasRole('admin')
        ) {
            abort(403);
        }

        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();

        return back()->with('success', 'Attachment deleted.');
    }

    public function download(Attachment $attachment)
    {
        if (!Storage::disk('public')->exists($attachment->path)) {
            abort(404);
        }

        return Storage::disk('public')->download($attachment->path, $attachment->filename);
    }
}
