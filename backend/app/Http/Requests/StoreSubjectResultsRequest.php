<?php

namespace App\Http\Requests;

use App\Models\ClassSubject;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectResultsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $classSubjectId = $this->input('class_subject_id');
        $caMax = 30;
        $examMax = 70;

        if ($classSubjectId) {
            $classSubject = ClassSubject::find($classSubjectId);
            if ($classSubject) {
                $caMax = $classSubject->ca_max ?? 30;
                $examMax = $classSubject->exam_max ?? 70;
            }
        }

        return [
            'term_id' => ['required', 'exists:terms,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'class_subject_id' => ['nullable', 'integer'],
            'results' => ['required', 'array'],
            'results.*.ca_score' => ['nullable', 'numeric', 'min:0', 'max:'.$caMax],
            'results.*.exam_score' => ['nullable', 'numeric', 'min:0', 'max:'.$examMax],
            'results.*.remark' => ['nullable', 'string', 'max:255'],
        ];
    }
}
