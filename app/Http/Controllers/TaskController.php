<?php
namespace App\Http\Controllers;
use App\Mail\TaskCompletedMail;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
class TaskController extends Controller {
 public function index(Request $request) { $tasks=Task::where('is_archived',false)->latest()->paginate(12);return view('tasks.index',compact('tasks')); }
 public function create() {return view('tasks.form',['task'=>new Task]);}
 public function store(Request $request) { $data=$this->validated($request);$request->user()->tasks()->create($data);return redirect()->route('tasks.index')->with('message','Task created.'); }
 public function show(Task $task) {abort_if($task->is_archived,404);return view('tasks.show',compact('task'));}
 public function edit(Request $request,Task $task) {$this->owned($request,$task);return view('tasks.form',compact('task'));}
 public function update(Request $request,Task $task) {
 $this->owned($request,$task);$data=$this->validated($request);$task->update($data);
 if($task->wasChanged('status') && $task->status==='done') {
  $recipient=config('mail.task_completed_to') ?: $request->user()->email;
  try {Mail::to($recipient)->send(new TaskCompletedMail($task));} catch (\Throwable $e) {Log::error('Task completion email failed',['task_id'=>$task->id,'error'=>$e->getMessage()]);return redirect()->route('tasks.show',$task)->with('message','Task updated, but email delivery failed. Check the mail settings and server log.');}
 }
 return redirect()->route('tasks.show',$task)->with('message','Task updated.');
 }
 public function destroy(Request $request,Task $task) {$this->owned($request,$task);$task->update(['is_archived'=>true]);return redirect()->route('tasks.index')->with('message','Task archived.');}
 private function owned(Request $request,Task $task):void {abort_if($task->is_archived || $task->user_id!==$request->user()->id,404);}
 private function validated(Request $request):array {return $request->validate(['title'=>['required','string','max:100'],'description'=>['nullable','string','max:2000'],'status'=>['required',Rule::in(['pending','in_progress','done'])],'due_date'=>['nullable','date']]);}
}
