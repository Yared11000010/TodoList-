<?php

namespace App\Livewire;

use App\Models\TodoList as ModelsTodoList;
use App\Models\TodoLists;
use Exception;
use GuzzleHttp\Exception\TooManyRedirectsException;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class TodoList extends Component
{
    use WithPagination;

    public $name;
    public $search;

    public $editingTodoID;

    #[Rule('requried|min:3|max:50')]
    public $editingTodoName;

    public function create(){

        $this->validate([
            'name'=>'required',
        ]);

        TodoLists::create([
            'name'=>$this->name,
        ]);

        $this->reset('name');

        $this->resetPage();

        request()->session()->flash('success','Saved!');

    }

    public function delete($todoID){
        try{
       TodoLists::findOrFail($todoID)->delete();
       request()->session()->flash('error','Deleted');
        }catch(Exception $e){
        request()->session()->flash('warning',"Can't Delete This Item");
        return;
        }
    }

    public function toggle($todoID){
       $todo= TodoLists::find($todoID);
       $todo->completed = !$todo->completed;
       $todo->save();

    }

    public function edit($todoID){
        $this->editingTodoID=$todoID;
        $this->editingTodoName=TodoLists::find($todoID)->name;

    }


    public function cancelTodo(){
        $this->reset(['editingTodoID','editingTodoName']);
    }

    public function updateTodo(){
       $this->validateOnly('editingTodoName', [
        'editingTodoName' => 'required', // Corrected spelling
    ]);
        TodoLists::find($this->editingTodoID)->update([
            'name'=>$this->editingTodoName
        ]);
        $this->cancelTodo();


    }
    public function render()
    {

        return view('livewire.todo-list',['todos'=>TodoLists::latest()->where('name','like',"%{$this->search}%")->paginate(5)]);
    }
}
