<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AssessmentTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'category',
            'time_limit',
            'type',
            'question',
            'media_url', // <-- Updated to media_url
            'option_1',
            'option_2',
            'option_3',
            'option_4',
            'correct_answer'
        ];
    }

    public function array(): array
    {
        return [
            // --- MCQ ---
            ['General Knowledge', '10', 'mcq', 'What is the capital of the Philippines?', '', 'Cebu', 'Manila', 'Davao', 'Iloilo', 'Option 2'],

            // --- TRUE/FALSE ---
            ['General Knowledge', '5', 'true_false', 'The earth is flat.', '', 'True', 'False', '', '', 'Option 2'],

            // --- MCQ with IMAGE ---
            ['General Knowledge', '10', 'mcq', 'What animal is shown in the picture?', 'https://upload.wikimedia.org/wikipedia/commons/4/4d/Cat_November_2010-1a.jpg', 'Dog', 'Cat', 'Bird', 'Fish', 'Option 2'],

            // --- CHECKBOX (Multiple Correct Answers) ---
            ['Science', '15', 'checkbox', 'Which of the following are planets?', '', 'Earth', 'Mars', 'Sun', 'Moon', 'Option 1,Option 2'],

            // --- TEXT (Short Answer) ---
            ['English', '10', 'text', 'Who wrote "Romeo and Juliet"?', '', '', '', '', '', 'William Shakespeare'],

            // --- TRUE/FALSE (Another Example) ---
            ['Science', '5', 'true_false', 'Water boils at 100°C.', '', 'True', 'False', '', '', 'Option 1'],

            // --- INSTRUCTION (No Answer Required) ---
            ['Instructions', '0', 'instruction', 'Please read all questions carefully before answering. Click "Next" to begin.', '', '', '', '', '', ''],

            // --- CHECKBOX (Another Example) ---
            ['Technology', '15', 'checkbox', 'What Primary Colors results to Green?', '', 'Blue', 'Red', 'Yellow', 'Orange', 'Option 1,Option 3'],

            // --- TEXT (Another Example) ---
            ['Math', '10', 'text', 'Solve for x: 2x + 4 = 10', '', '', '', '', '', '3'],
        ];
    }
}