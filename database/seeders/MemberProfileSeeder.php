<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class MemberProfileSeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            'Lagos', 'Abuja', 'Port Harcourt', 'Kano', 'Ibadan', 
            'Benin City', 'Enugu', 'Kaduna', 'Jos', 'Calabar'
        ];

        $occupations = [
            'Software Developer', 'Business Analyst', 'Teacher', 'Civil Engineer',
            'Lawyer', 'Medical Doctor', 'Accountant', 'Marketing Specialist',
            'Project Manager', 'Consultant', 'Entrepreneur', 'Data Scientist',
            'Graphic Designer', 'Financial Analyst', 'Human Resources Manager'
        ];

        $aboutTexts = [
            'Passionate about community development and youth empowerment through technology and innovation.',
            'Dedicated professional committed to democratic participation and positive social change.',
            'Experienced leader focused on building stronger communities through civic engagement.',
            'Advocate for transparency and accountability in governance and public service delivery.',
            'Committed to fostering innovation and entrepreneurship among young Nigerians.',
            'Believer in the power of education and mentorship to transform communities.',
            'Focused on sustainable development and environmental conservation initiatives.',
            'Champion of gender equality and women empowerment in professional spaces.',
            'Dedicated to promoting financial literacy and economic empowerment in rural communities.',
            'Passionate advocate for youth development and leadership capacity building.',
            'Committed to advancing healthcare access and quality in underserved communities.',
            'Focused on leveraging technology to solve local challenges and create opportunities.'
        ];

        User::where('status', 'approved')
            ->whereNull('city')
            ->chunk(50, function ($users) use ($cities, $occupations, $aboutTexts) {
                foreach ($users as $user) {
                    $user->update([
                        'city' => $cities[array_rand($cities)],
                        'occupation' => $occupations[array_rand($occupations)],
                        'about_me' => $aboutTexts[array_rand($aboutTexts)]
                    ]);
                }
            });
    }
}