<?php

namespace App\Services;

use App\Models\Student;

use Illuminate\Support\Facades\Storage;

class StudentService
{
    private function deleteFile($filename)
    {
        if($filename != NULL && Storage::exists('public/pictures/'.$filename))
        {
            Storage::delete('public/pictures/'.$filename);
        }
    }

    private function save(Student $student, $data)
    {
        $student->firstname = $data->firstname;
        $student->lastname = $data->lastname;
        $student->save();

        $this->deleteFile($student->photo);

        $picture = $data->file('photo');
        $student->photo = $student->id.'.'.$picture->getClientOriginalExtension();
        Storage::putFileAs('public/pictures', $picture, $student->photo);

        $student->touch();
        $student->save();
    }

    public function add($data)
    {
        $this->save(new Student(), $data);
    }

    public function get($id)
    {
        return Student::find($id);
    }

    public function getAll()
    {
        return Student::all();
    }

    public function delete($id)
    {
        $student = Student::find($id);

        $this->deleteFile($student->photo);

        $student->delete();
    }

    public function exists($id)
    {
        return Student::find($id) != NULL;
    }

    public function edit($data, $id)
    {
        $this->save($this->get($id), $data);
    }
}
