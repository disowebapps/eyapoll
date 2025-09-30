<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class FullProfileSeeder extends Seeder
{
    public function run(): void
    {
        $bios = [
            'Passionate advocate for youth empowerment and community development. With over 5 years of experience in project management and community organizing, I believe in the power of collective action to create positive change. I have led several initiatives focused on education, entrepreneurship, and civic engagement among young people.',
            'Dedicated professional committed to advancing democratic participation and social justice. My background in law and public policy has equipped me with the skills to navigate complex social issues and advocate for meaningful reform. I am particularly interested in electoral processes and governance transparency.',
            'Experienced leader in the technology sector with a focus on using innovation to solve local challenges. I have worked with various startups and established companies to develop solutions that address community needs. My expertise spans software development, data analysis, and digital transformation.',
            'Community organizer and social entrepreneur passionate about sustainable development. I have founded two non-profit organizations focused on environmental conservation and youth mentorship. My work involves building partnerships between communities, government, and private sector stakeholders.',
            'Healthcare professional dedicated to improving access to quality medical services in underserved communities. With a background in public health and healthcare administration, I work to bridge gaps in healthcare delivery and advocate for health equity policies.',
            'Education specialist committed to transforming learning experiences for young people. I have developed innovative curricula and training programs that combine traditional knowledge with modern pedagogical approaches. My focus is on making education more relevant and accessible.',
            'Financial literacy advocate and microfinance expert working to promote economic empowerment in rural communities. I have designed and implemented several financial inclusion programs that have helped hundreds of individuals start and grow their businesses.',
            'Media and communications professional passionate about storytelling for social change. I use various platforms to amplify voices from marginalized communities and promote dialogue on important social issues. My work spans journalism, content creation, and strategic communications.'
        ];

        $skills = [
            'Leadership, Public Speaking, Project Management, Strategic Planning, Community Organizing',
            'Legal Research, Policy Analysis, Advocacy, Negotiation, Public Administration',
            'Software Development, Data Analysis, Digital Marketing, Innovation Management, Technical Writing',
            'Non-profit Management, Fundraising, Partnership Development, Environmental Policy, Youth Mentoring',
            'Healthcare Administration, Public Health, Health Policy, Community Health, Medical Research',
            'Curriculum Development, Training Design, Educational Technology, Youth Development, Research',
            'Financial Planning, Microfinance, Business Development, Economic Analysis, Training',
            'Content Creation, Social Media, Journalism, Strategic Communications, Digital Storytelling'
        ];

        $qualifications = ['bachelor', 'master', 'phd', 'professional', 'diploma'];
        $fields = [
            'Political Science', 'Law', 'Computer Science', 'Business Administration', 'Public Health',
            'Education', 'Economics', 'Mass Communication', 'Engineering', 'Social Work',
            'International Relations', 'Environmental Science', 'Medicine', 'Psychology'
        ];

        User::whereIn('status', ['approved', 'accredited'])
            ->chunk(50, function ($users) use ($bios, $skills, $qualifications, $fields) {
                foreach ($users as $user) {
                    $user->update([
                        'bio' => $bios[array_rand($bios)],
                        'skills' => $skills[array_rand($skills)],
                        'highest_qualification' => $qualifications[array_rand($qualifications)],
                        'field_of_study' => $fields[array_rand($fields)],
                        'employment_status' => ['employed', 'self_employed', 'student'][array_rand(['employed', 'self_employed', 'student'])],
                        'email_public' => true,
                        'phone_public' => true,
                    ]);
                }
            });
    }
}