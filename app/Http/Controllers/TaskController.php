<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\TaskService;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use ApiResponse;

    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function index()
    {
        try {
            $tasks = Task::where('is_active', true)->get();
            return $this->successResponse($tasks);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch tasks', 'FETCH_ERROR');
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'reward' => 'required|numeric|min:0',
                'timeEstimate' => 'required|string',
                'category' => 'required|string',
                'difficulty' => 'required|in:Easy,Medium,Hard',
                'timeInSeconds' => 'required|integer|min:0',
                'steps' => 'required|array',
                'approvalType' => 'required|in:automatic,manual',
            ]);

            $task = Task::create([
                'title' => $request->title,
                'description' => $request->description,
                'reward' => $request->reward,
                'time_estimate' => $request->timeEstimate,
                'category' => $request->category,
                'difficulty' => $request->difficulty,
                'time_in_seconds' => $request->timeInSeconds,
                'steps' => $request->steps,
                'approval_type' => $request->approvalType,
            ]);

            return $this->successResponse($task, 'Task created successfully', 201);
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 'VALIDATION_ERROR');
        }
    }

    public function submit(Request $request, Task $task)
    {
        try {
            $request->validate([
                'screenshotUrl' => 'required|url',
            ]);

            $result = $this->taskService->submitTask($task, $request->screenshotUrl);
            return $this->successResponse($result, 'Task submitted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to submit task', 'SUBMISSION_ERROR');
        }
    }

    public function review(Request $request, Task $task)
    {
        try {
            $request->validate([
                'submissionId' => 'required|exists:task_submissions,id',
                'status' => 'required|in:approved,rejected',
            ]);

            $result = $this->taskService->reviewSubmission(
                $task,
                $request->submissionId,
                $request->status
            );
            return $this->successResponse($result, 'Review submitted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to review submission', 'REVIEW_ERROR');
        }
    }
}