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
            'section_type',      // 'lesson' or 'exam'
            'section_title',     // Name of the Lesson/Exam
            'type',              // 'content', 'mcq', 'true_false', 'checkbox', or 'text'
            'content_text',      // The lesson text or the question text
            'media_url',         // Optional URL to image, video, or audio
            'is_case_sensitive', // TRUE or FALSE (Mainly for 'text' questions)
            'option_1',
            'option_2',
            'option_3',
            'option_4',
            'correct_answer'     // e.g., 'Option 1', 'Option 2', or 'Option 1, Option 3'
        ];
    }

    public function array(): array
    {
        return [
            // --- LESSON 1: Basic English Introduction ---
            ['lesson', 'Chapter 1: Introduction to English', 'content', 'English is a widely used language around the world. It is used for communication, education, and business.', '', 'FALSE', '', '', '', '', ''],
            ['lesson', 'Chapter 1: Introduction to English', 'mcq', 'Which of the following is a vowel?', '', 'FALSE', 'B', 'C', 'A', 'D', 'Option 3'],

            // --- LESSON 2: Parts of Speech ---
            ['lesson', 'Chapter 2: Parts of Speech', 'content', 'A noun is a word that names a person, place, or thing. A verb is a word that shows action.', '', 'FALSE', '', '', '', '', ''],
            ['lesson', 'Chapter 2: Parts of Speech', 'mcq', 'Which word is a noun?', '', 'FALSE', 'Run', 'Dog', 'Quickly', 'Blue', 'Option 2'],
            ['lesson', 'Chapter 2: Parts of Speech', 'true_false', 'A verb shows action.', '', 'FALSE', 'True', 'False', '', '', 'Option 1'],

            // --- LESSON 3: Simple Sentences ---
            ['lesson', 'Chapter 3: Simple Sentences', 'content', 'A sentence is a group of words that expresses a complete thought. It usually has a subject and a verb.', '', 'FALSE', '', '', '', '', ''],
            ['lesson', 'Chapter 3: Simple Sentences', 'mcq', 'Which of the following is a complete sentence?', '', 'FALSE', 'Running fast', 'The dog barks', 'Blue sky', 'Very quickly', 'Option 2'],
            ['lesson', 'Chapter 3: Simple Sentences', 'checkbox', 'Which of the following are sentences? (Select all that apply)', '', 'FALSE', 'She is happy.', 'Running fast', 'They play.', 'Blue sky', 'Option 1, Option 3'],

            // --- LESSON 4: Fill in the Blank (Using Case Sensitive TRUE) ---
            ['lesson', 'Chapter 4: Fill in the Blank', 'text', 'Fill in the blank: She ___ going to school.', '', 'TRUE', 'is', '', '', '', 'Option 1, Option 2'],

            // --- FINAL EXAM ---
            ['exam', 'Final Examination', 'true_false', 'A sentence must have a subject and a verb.', '', 'FALSE', 'True', 'False', '', '', 'Option 1'],
            ['exam', 'Final Examination', 'mcq', 'Which word is a verb?', '', 'FALSE', 'Apple', 'Run', 'Table', 'Chair', 'Option 2'],
        ];
    }
}