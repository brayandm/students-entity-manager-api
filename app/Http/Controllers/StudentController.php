<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\StudentService;

class StudentController extends Controller
{
    private StudentService $studentsService;

    public function __construct(StudentService $studentsService)
    {
        $this->studentsService = $studentsService;
    }

    private function validateCorrectStudent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|max:120',
            'lastname' => 'required|max:120',
            // 'email' => 'required|email|max:120',
            // 'photo' => 'required|image',
            // 'birthdate' => 'required|date',
            // 'address' => 'required|max:255',
            // 'score' => 'required|numeric|between:0,999999'
        ]);

        return $validator;
    }

    public function getStudents()
    {
        return response()->json(['students' => $this->studentsService->getAll()]);
    }

    public function getStudent($id)
    {
        if(!$this->studentsService->exists($id))
        {
            return response()->json(['errors' => 'The student with id='.$id.' doesn\'t exist'], 400);
        }

        return response()->json(['students' => $this->studentsService->get($id)]);
    }

    public function addStudent(Request $request)
    {
        $validator = $this->validateCorrectStudent($request);

        if($validator->fails())
        {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $this->studentsService->add($request);

        return response()->json([]);
    }

    public function deleteStudent($id)
    {
        if(!$this->studentsService->exists($id))
        {
            return response()->json(['errors' => 'The student with id='.$id.' doesn\'t exist'], 400);
        }

        $this->studentsService->delete($id);

        return response()->json();
    }

    public function editStudent(Request $request, $id)
    {
        if(!$this->studentsService->exists($id))
        {
            return response()->json(['errors' => 'The student with id='.$id.' doesn\'t exist'], 400);
        }

        $validator = $this->validateCorrectStudent($request);

        if($validator->fails())
        {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $this->studentsService->edit($id, $request);

        return response()->json([]);
    }
}
