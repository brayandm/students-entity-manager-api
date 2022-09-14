<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\StudentService;

class StudentController extends Controller
{
    private StudentService $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    private function validateCorrectStudent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|max:120',
            'lastname' => 'required|max:120',
            'email' => 'required|email|max:120',
            'photo' => 'image',
            'birthdate' => 'required|date',
            'address' => 'required|max:255',
            'score' => 'required|numeric|between:0,999999'
        ]);

        return $validator;
    }

    public function getStudents()
    {
        return response()->json(['students' => $this->studentService->getAll()]);
    }

    public function getStudent($id)
    {
        if(!$this->studentService->exists($id))
        {
            return response()->json(['errors' => 'The student with id='.$id.' doesn\'t exist'], 400);
        }

        return response()->json(['students' => $this->studentService->get($id)]);
    }

    public function addStudent(Request $request)
    {
        $validator = $this->validateCorrectStudent($request);

        if($validator->fails())
        {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $this->studentService->add($request);

        return response()->json([]);
    }

    public function deleteStudent($id)
    {
        if(!$this->studentService->exists($id))
        {
            return response()->json(['errors' => 'The student with id='.$id.' doesn\'t exist'], 400);
        }

        $this->studentService->delete($id);

        return response()->json();
    }

    public function editStudent(Request $request, $id)
    {
        if(!$this->studentService->exists($id))
        {
            return response()->json(['errors' => 'The student with id='.$id.' doesn\'t exist'], 400);
        }

        $validator = $this->validateCorrectStudent($request);

        if($validator->fails())
        {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $this->studentService->edit($request, $id);

        return response()->json([]);
    }

    public function getPhoto($photo)
    {
        return $this->studentService->getPhoto($photo);
    }
}
