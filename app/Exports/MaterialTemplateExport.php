<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MaterialTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'section_type',  // 'lesson' or 'exam'
            'section_title', // Name of the Lesson/Exam
            'type',          // 'content', 'mcq', 'true_false', etc.
            'content_text',  // The lesson text or the question text
            'media_url',
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
            // --- LESSON 1: Mixture of Content and Practice Quiz ---
            ['lesson', 'Chapter 1: Basics', 'content', 'Please watch the attached introductory video and read the following text before proceeding to the practice quiz.', 'https://example.com/intro.mp4', '', '', '', '', ''],
            ['lesson', 'Chapter 1: Basics', 'content', 'Laravel is a PHP framework used for building web applications.', '', '', '', '', '', ''],
            ['lesson', 'Chapter 1: Basics', 'mcq', 'What is the primary language used for web styling?', '', 'HTML', 'CSS', 'JavaScript', 'PHP', 'Option 2'],
            
            // --- LESSON 2: Just Content ---
            ['lesson', 'Chapter 2: Advanced Data', 'content', 'Graphs are non-linear data structures consisting of nodes and edges.', '', '', '', '', '', ''],
            
            // --- FINAL EXAM: Strictly Graded Questions ---
            ['exam', 'Midterm Examination', 'true_false', 'Laravel is built on top of JavaScript.', '', 'True', 'False', '', '', 'Option 2'],
            ['exam', 'Midterm Examination', 'mcq', 'Which data structure uses LIFO (Last In First Out)?', '', 'Queue', 'Array', 'Stack', 'Tree', 'Option 3'],
        ];
    } 
}