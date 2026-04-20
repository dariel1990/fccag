<?php

namespace Database\Seeders;

use App\Models\SiActivity;
use App\Models\SiAttendance;
use Illuminate\Database\Seeder;

class SiActivitySeeder extends Seeder
{
    /**
     * Member ID map (ph_id => si_members.id)
     *
     * ph_id 177  => id 18  Lyra Hinlas          (caregiver: J-C-LYN Hinlas)
     * ph_id 229  => id 14  Jainah Absalon        (caregiver: Harlene Absalon)
     * ph_id 230  => id 11  Mateo Apao            (caregiver: Marites Palomar)
     * ph_id 231  => id 10  Lemchelle Orito       (caregiver: Michelle Orito)
     * ph_id 232  => id 21  Jona May Pilapil      (caregiver: Ronelyn Hungayo)
     * ph_id 233  => id 16  Nathalia Ixia Gambong (caregiver: Noime Ronquillo)
     * ph_id 234  => id  9  Angel Gwyn Bernales   (caregiver: Rhea Angel Bernales)
     * ph_id 235  => id 20  Jessica Marjas        (caregiver: Jeselle Dumalaga)
     * ph_id 236  => id  8  Readel Layno          (caregiver: Hazel An Orito)
     * ph_id 237  => id 12  Jian Mondalo          (caregiver: Cristy Mondalo)
     * ph_id 239  => id 15  Kathryne Ameera Gomez (caregiver: Anita Ajoc)
     * ph_id 240  => id 19  Bryce Yuan Bancale    (caregiver: Robles Aubry)
     * ph_id 241  => id 13  Eliana Mae Caseris    (caregiver: Estela Mae Montenegro)
     * ph_id 242  => id 17  Mark Jayden Lozada    (caregiver: Mayet Midrano)
     *
     * Category IDs:
     * 1 = Life Class  (weight 0.30)
     * 2 = Sunday Service (weight 0.20)
     * 3 = Home Visitation (weight 0.15)
     * 4 = Major Activity (weight 0.35)
     */
    public function run(): void
    {
        $activities = [
            // ── JULY 2025 ──────────────────────────────────────────────────
            [
                'si_activity_category_id' => 4,
                'title' => 'Orientation of New Set of Participants',
                'speaker' => 'Ptra. Mole',
                'topic' => 'Trust in the Lord',
                'memory_verse' => 'Proverbs 3:5-7',
                'conducted_at' => '2025-07-18',
                'attendance' => [
                    8 => 'present', 9 => 'absent', 10 => 'present', 11 => 'present',
                    12 => 'present', 13 => 'present', 14 => 'absent', 15 => 'present',
                    16 => 'present', 17 => 'present', 18 => 'present', 19 => 'present',
                    20 => 'absent', 21 => 'absent',
                ],
            ],
            [
                'si_activity_category_id' => 2,
                'title' => 'Last Sunday Service',
                'speaker' => 'Ptra. Mole',
                'topic' => 'Have Faith in God',
                'memory_verse' => '1 Kings 17:9-16',
                'conducted_at' => '2025-07-27',
                'attendance' => [
                    8 => 'present', 9 => 'absent', 10 => 'absent', 11 => 'present',
                    12 => 'absent', 13 => 'present', 14 => 'absent', 15 => 'absent',
                    16 => 'present', 17 => 'present', 18 => 'present', 19 => 'absent',
                    20 => 'present', 21 => 'present',
                ],
            ],
            [
                'si_activity_category_id' => 3,
                'title' => 'July 2025 Home Visitation',
                'speaker' => null,
                'topic' => null,
                'memory_verse' => null,
                'conducted_at' => '2025-07-31',
                'attendance' => [
                    8 => 'present', 9 => 'present', 10 => 'absent', 11 => 'present',
                    12 => 'absent', 13 => 'absent', 14 => 'present', 15 => 'absent',
                    16 => 'present', 17 => 'absent', 18 => 'present', 19 => 'present',
                    20 => 'present', 21 => 'present',
                ],
            ],
            // ── AUGUST 2025 ────────────────────────────────────────────────
            [
                'si_activity_category_id' => 1,
                'title' => 'Life Class Session Zone 1',
                'speaker' => 'Ptra. Mole',
                'topic' => 'Responsive Caregiving',
                'memory_verse' => 'Isaiah 65:24',
                'conducted_at' => '2025-08-14',
                'attendance' => [
                    8 => 'present', 9 => 'present', 10 => 'present', 11 => 'absent',
                ],
            ],
            [
                'si_activity_category_id' => 1,
                'title' => 'Life Class Session Zone 2',
                'speaker' => 'Ptra. Mole',
                'topic' => 'Responsive Giving',
                'memory_verse' => 'Isaiah 65:24',
                'conducted_at' => '2025-08-22',
                'attendance' => [
                    12 => 'present', 14 => 'absent', 17 => 'present', 19 => 'present', 20 => 'absent',
                ],
            ],
            [
                'si_activity_category_id' => 1,
                'title' => 'Life Class Session Zone 3',
                'speaker' => 'Ptra. Mole',
                'topic' => 'Responsive Giving',
                'memory_verse' => 'Isaiah 65:25',
                'conducted_at' => '2025-08-28',
                'attendance' => [
                    13 => 'present', 15 => 'absent', 16 => 'present', 18 => 'present', 21 => 'present',
                ],
            ],
            [
                'si_activity_category_id' => 3,
                'title' => 'August 2025 Home Visitation',
                'speaker' => null,
                'topic' => null,
                'memory_verse' => null,
                'conducted_at' => '2025-08-29',
                'attendance' => [
                    8 => 'present', 9 => 'present', 10 => 'present', 11 => 'absent',
                    12 => 'present', 13 => 'present', 14 => 'present', 15 => 'present',
                    16 => 'absent', 17 => 'absent', 18 => 'absent', 19 => 'present',
                    20 => 'present', 21 => 'absent',
                ],
            ],
            [
                'si_activity_category_id' => 4,
                'title' => 'Postpartum & First 1000 Days',
                'speaker' => 'Rodney Ben Z. Mantilla',
                'topic' => 'Postpartum & First 1000 Days',
                'memory_verse' => null,
                'conducted_at' => '2025-08-31',
                'attendance' => [
                    8 => 'absent', 9 => 'absent', 10 => 'present', 11 => 'present',
                    12 => 'present', 13 => 'present', 14 => 'absent', 15 => 'present',
                    16 => 'absent', 17 => 'present', 18 => 'present', 19 => 'absent',
                    20 => 'present', 21 => 'present',
                ],
            ],
            // ── SEPTEMBER 2025 ─────────────────────────────────────────────
            [
                'si_activity_category_id' => 1,
                'title' => 'Life Class Session Zone 1',
                'speaker' => 'Ptra. Mole',
                'topic' => 'Principles of Growth & Development',
                'memory_verse' => 'Psalms 127:3',
                'conducted_at' => '2025-09-11',
                'attendance' => [
                    8 => 'absent', 9 => 'present', 10 => 'present', 11 => 'present',
                ],
            ],
            [
                'si_activity_category_id' => 1,
                'title' => 'Joint Life Class Session Zone 2&3',
                'speaker' => 'Ptra. Mole',
                'topic' => 'Principles of Growth & Development',
                'memory_verse' => 'Psalms 127:3',
                'conducted_at' => '2025-09-19',
                'attendance' => [
                    8 => 'present', 9 => 'absent', 10 => 'present', 11 => 'present',
                    12 => 'absent', 13 => 'present', 14 => 'present', 15 => 'present',
                    16 => 'present', 17 => 'present', 18 => 'present', 19 => 'present',
                    20 => 'present', 21 => 'absent',
                ],
            ],
            [
                'si_activity_category_id' => 3,
                'title' => 'September 2025 Home Visitation',
                'speaker' => null,
                'topic' => null,
                'memory_verse' => null,
                'conducted_at' => '2025-09-30',
                'attendance' => [
                    8 => 'present', 9 => 'present', 10 => 'absent', 11 => 'present',
                    12 => 'present', 13 => 'present', 14 => 'present', 15 => 'absent',
                    16 => 'present', 17 => 'present', 18 => 'present', 19 => 'absent',
                    20 => 'absent', 21 => 'absent',
                ],
            ],
            // ── OCTOBER 2025 ───────────────────────────────────────────────
            [
                'si_activity_category_id' => 2,
                'title' => 'Sunday Service - October 26',
                'speaker' => null,
                'topic' => 'Sunday Service',
                'memory_verse' => null,
                'conducted_at' => '2025-10-26',
                'attendance' => [
                    8 => 'present', 9 => 'absent', 10 => 'present', 11 => 'present',
                    12 => 'absent', 13 => 'present', 14 => 'absent', 15 => 'absent',
                    16 => 'present', 17 => 'absent', 18 => 'present', 19 => 'absent',
                    20 => 'present', 21 => 'present',
                ],
            ],
            [
                'si_activity_category_id' => 3,
                'title' => 'October 2025 Home Visitation',
                'speaker' => null,
                'topic' => null,
                'memory_verse' => null,
                'conducted_at' => '2025-10-31',
                'attendance' => [
                    8 => 'absent', 9 => 'absent', 10 => 'present', 11 => 'present',
                    12 => 'present', 13 => 'absent', 14 => 'absent', 15 => 'present',
                    16 => 'absent', 17 => 'absent', 18 => 'present', 19 => 'present',
                    20 => 'absent', 21 => 'present',
                ],
            ],
            // ── NOVEMBER 2025 ──────────────────────────────────────────────
            [
                'si_activity_category_id' => 3,
                'title' => 'November 2025 Home Visitation',
                'speaker' => null,
                'topic' => null,
                'memory_verse' => null,
                'conducted_at' => '2025-11-28',
                'attendance' => [
                    8 => 'present', 9 => 'present', 10 => 'present', 11 => 'present',
                    12 => 'absent', 13 => 'present', 14 => 'absent', 15 => 'present',
                    16 => 'present', 17 => 'absent', 18 => 'present', 19 => 'absent',
                    20 => 'present', 21 => 'absent',
                ],
            ],
            [
                'si_activity_category_id' => 4,
                'title' => 'Thanksgiving Day Celebration',
                'speaker' => 'Ptr. Andrei',
                'topic' => 'Streams in the Deserts',
                'memory_verse' => 'Isaiah 43:19',
                'conducted_at' => '2025-11-30',
                'attendance' => [
                    8 => 'present', 9 => 'present', 10 => 'present', 11 => 'present',
                    12 => 'absent', 13 => 'absent', 14 => 'present', 15 => 'absent',
                    16 => 'absent', 17 => 'absent', 18 => 'present', 19 => 'absent',
                    20 => 'present', 21 => 'present',
                ],
            ],
            // ── DECEMBER 2025 ──────────────────────────────────────────────
            [
                'si_activity_category_id' => 3,
                'title' => 'December 2025 Home Visitation',
                'speaker' => null,
                'topic' => null,
                'memory_verse' => null,
                'conducted_at' => '2025-12-19',
                'attendance' => [
                    10 => 'present', 13 => 'present', 15 => 'present',
                    17 => 'present', 19 => 'present',
                ],
            ],
            [
                'si_activity_category_id' => 4,
                'title' => 'Year End Celebration',
                'speaker' => 'Ptra. Jacquelyn / Mam. Gelian Taripe',
                'topic' => 'A Year End Thanksgiving for New Life',
                'memory_verse' => null,
                'conducted_at' => '2025-12-21',
                'attendance' => [
                    8 => 'present', 9 => 'present', 10 => 'present', 11 => 'present',
                    12 => 'present', 13 => 'present', 14 => 'present', 15 => 'present',
                    16 => 'present', 17 => 'present', 18 => 'present', 19 => 'present',
                    20 => 'gave_birth', 21 => 'present',
                ],
            ],
            // ── JANUARY 2026 ───────────────────────────────────────────────
            [
                'si_activity_category_id' => 1,
                'title' => 'Life Class Zone 1',
                'speaker' => 'Ptra. Mole',
                'topic' => "Immerse in God's Word",
                'memory_verse' => 'Psalms 119:11',
                'conducted_at' => '2026-01-22',
                'attendance' => [
                    8 => 'present', 9 => 'present', 10 => 'present', 11 => 'present',
                    14 => 'absent',
                ],
            ],
            [
                'si_activity_category_id' => 1,
                'title' => 'Life Class Zone 2',
                'speaker' => 'Ptra. Mole',
                'topic' => "Immerse in God's Word",
                'memory_verse' => 'Psalms 119:11',
                'conducted_at' => '2026-01-23',
                'attendance' => [
                    12 => 'absent', 15 => 'present', 17 => 'present',
                    19 => 'present', 20 => 'present',
                ],
            ],
            [
                'si_activity_category_id' => 1,
                'title' => 'Life Class Zone 3',
                'speaker' => 'Ptra. Mole',
                'topic' => "Immerse in God's Word",
                'memory_verse' => 'Psalms 119:11',
                'conducted_at' => '2026-01-24',
                'attendance' => [
                    13 => 'present', 16 => 'absent', 21 => 'present',
                ],
            ],
            [
                'si_activity_category_id' => 4,
                'title' => 'Last Sunday Church',
                'speaker' => 'Ptr. Andrei',
                'topic' => "Significant of God's Word",
                'memory_verse' => 'John 14:15',
                'conducted_at' => '2026-01-25',
                'attendance' => [
                    9 => 'absent', 14 => 'present', 19 => 'present',
                    20 => 'present', 21 => 'present',
                ],
            ],
            [
                'si_activity_category_id' => 3,
                'title' => 'January 2026 Home Visitation',
                'speaker' => null,
                'topic' => null,
                'memory_verse' => null,
                'conducted_at' => '2026-01-30',
                'attendance' => [
                    16 => 'present', 17 => 'present',
                ],
            ],
            [
                'si_activity_category_id' => 4,
                'title' => 'Bible Month Celebration',
                'speaker' => 'Rev. Deligero',
                'topic' => "Immerse in God's Word",
                'memory_verse' => '1 Cor. 15:58',
                'conducted_at' => '2026-01-31',
                'attendance' => [
                    8 => 'present', 9 => 'present', 10 => 'present', 11 => 'present',
                    12 => 'child_sick', 13 => 'present', 14 => 'present', 15 => 'present',
                    16 => 'child_sick', 17 => 'child_sick', 18 => 'present', 19 => 'absent',
                    20 => 'present', 21 => 'present',
                ],
            ],
            // ── FEBRUARY 2026 ──────────────────────────────────────────────
            [
                'si_activity_category_id' => 1,
                'title' => 'Life Class Zone 3',
                'speaker' => 'Sis. Cristina Salvame',
                'topic' => 'Loving Our Spouse',
                'memory_verse' => null,
                'conducted_at' => '2026-02-14',
                'attendance' => [
                    13 => 'present', 16 => 'present', 21 => 'present',
                ],
            ],
            [
                'si_activity_category_id' => 1,
                'title' => 'Life Class Zone 1',
                'speaker' => 'Sis. Cristina Salvame',
                'topic' => 'Loving God with People',
                'memory_verse' => null,
                'conducted_at' => '2026-02-21',
                'attendance' => [
                    8 => 'present', 10 => 'present', 11 => 'present',
                    14 => 'present', 21 => 'present',
                ],
            ],
            [
                'si_activity_category_id' => 3,
                'title' => 'February 2026 Home Visitation',
                'speaker' => null,
                'topic' => null,
                'memory_verse' => null,
                'conducted_at' => '2026-02-27',
                'attendance' => [
                    8 => 'present', 9 => 'present', 10 => 'present', 11 => 'present',
                    12 => 'present', 13 => 'absent', 14 => 'absent', 15 => 'present',
                    16 => 'absent', 17 => 'present', 18 => 'absent', 19 => 'absent',
                    20 => 'absent', 21 => 'absent',
                ],
            ],
            // ── MARCH 2026 ─────────────────────────────────────────────────
            [
                'si_activity_category_id' => 2,
                'title' => 'Seek God First',
                'speaker' => null,
                'topic' => 'Seek God First',
                'memory_verse' => null,
                'conducted_at' => '2026-03-06',
                'attendance' => [
                    8 => 'present', 9 => 'present', 10 => 'present', 11 => 'present',
                    12 => 'present', 13 => 'absent', 14 => 'present', 15 => 'present',
                    16 => 'absent', 17 => 'present', 18 => 'present', 19 => 'present',
                    20 => 'present', 21 => 'present',
                ],
            ],
            [
                'si_activity_category_id' => 1,
                'title' => 'Unshaken Faith in a Shaken World',
                'speaker' => null,
                'topic' => 'Unshaken Faith in a Shaken World',
                'memory_verse' => null,
                'conducted_at' => '2026-03-13',
                'attendance' => [
                    8 => 'present', 9 => 'present', 10 => 'present',
                ],
            ],
            [
                'si_activity_category_id' => 2,
                'title' => 'Blessed Womb Blessed Home',
                'speaker' => null,
                'topic' => 'Blessed Womb Blessed Home',
                'memory_verse' => null,
                'conducted_at' => '2026-03-15',
                'attendance' => [
                    8 => 'present', 9 => 'present', 10 => 'present', 11 => 'present',
                    12 => 'absent', 13 => 'present', 14 => 'present', 15 => 'present',
                    16 => 'absent', 17 => 'present', 18 => 'present', 19 => 'absent',
                    20 => 'present', 21 => 'present',
                ],
            ],
            [
                'si_activity_category_id' => 1,
                'title' => "Living in God's Love",
                'speaker' => null,
                'topic' => "Living in God's Love",
                'memory_verse' => null,
                'conducted_at' => '2026-03-20',
                'attendance' => [
                    12 => 'present', 15 => 'absent',
                ],
            ],
            [
                'si_activity_category_id' => 3,
                'title' => 'March 2026 Home Visitation',
                'speaker' => null,
                'topic' => null,
                'memory_verse' => null,
                'conducted_at' => '2026-03-25',
                'attendance' => [
                    8 => 'absent', 9 => 'absent', 10 => 'absent', 11 => 'absent',
                    12 => 'absent', 13 => 'present', 14 => 'present', 15 => 'absent',
                    16 => 'present', 17 => 'absent', 18 => 'absent', 19 => 'present',
                    20 => 'present', 21 => 'present',
                ],
            ],
        ];

        foreach ($activities as $data) {
            $attendance = $data['attendance'];
            unset($data['attendance']);

            $activity = SiActivity::create($data);
            $activity->assignedMembers()->sync(array_keys($attendance));

            $records = [];

            foreach ($attendance as $memberId => $status) {
                $records[] = [
                    'si_activity_id' => $activity->id,
                    'si_member_id' => $memberId,
                    'status' => $status,
                    'remarks' => null,
                ];
            }

            SiAttendance::insert($records);
        }

        $this->command->info('Seeded '.count($activities).' SI activities with '.SiAttendance::count().' attendance records.');
    }
}
