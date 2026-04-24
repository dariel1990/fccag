<?php

namespace Database\Seeders;

use App\Models\Setlist;
use App\Models\Song;
use App\Models\User;
use Illuminate\Database\Seeder;

class MusicSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('is_superadmin', true)->first() ?? User::first();

        $songs = [
            [
                'title' => 'Amazing Grace',
                'artist' => 'Traditional',
                'composer' => 'John Newton',
                'original_key' => 'G',
                'tempo' => 76,
                'time_signature' => '3/4',
                'lyrics_with_chords' => "[Verse 1]\nG            G7          C             G\nAmazing grace how sweet the sound\nG              Em        D\nThat saved a wretch like me\nG            G7          C             G\nI once was lost but now am found\nG           D           G\nWas blind but now I see\n\n[Verse 2]\nG            G7           C            G\n'Twas grace that taught my heart to fear\nG           Em         D\nAnd grace my fears relieved\nG              G7           C            G\nHow precious did that grace appear\nG           D           G\nThe hour I first believed",
                'notes' => 'Traditional hymn. Play slowly and reverently.',
            ],
            [
                'title' => 'How Great Is Our God',
                'artist' => 'Chris Tomlin',
                'composer' => 'Chris Tomlin / Jesse Reeves / Ed Cash',
                'original_key' => 'C',
                'tempo' => 76,
                'time_signature' => '4/4',
                'lyrics_with_chords' => "[Verse]\nC                  Am\nThe splendor of the King\n        F\nClothed in majesty\n             C\nLet all the earth rejoice\n     Am\nAll the earth rejoice\nC                  Am\nHe wraps himself in light\n             F\nAnd darkness tries to hide\n                  G\nAnd trembles at his voice\n          G\nTrembles at his voice\n\n[Chorus]\nC             Am\nHow great is our God\n         F                  C\nSing with me how great is our God\n     Am                    F\nAnd all will see how great how great\n     C\nIs our God",
                'notes' => 'Modern worship anthem. Congregation favorite.',
            ],
            [
                'title' => 'What A Beautiful Name',
                'artist' => 'Hillsong Worship',
                'composer' => 'Ben Fielding / Brooke Ligertwood',
                'original_key' => 'D',
                'tempo' => 68,
                'time_signature' => '4/4',
                'lyrics_with_chords' => "[Verse 1]\nD                          A\nYou were the Word at the beginning\nBm                       G\nOne with God the Lord Most High\nD                          A\nYour hidden glory in creation\nBm                    G\nNow revealed in You our Christ\n\n[Chorus]\nG                D\nWhat a beautiful name it is\nG                D\nWhat a beautiful name it is\n    Bm          A\nThe name of Jesus Christ my King\nG                D\nWhat a beautiful name it is\n    A                  Bm\nNothing compares to this\nG                     A\nWhat a beautiful name it is\n         D\nThe name of Jesus",
                'notes' => 'Key change option: up 2 semitones to E for the bridge.',
            ],
            [
                'title' => 'Oceans (Where Feet May Fail)',
                'artist' => 'Hillsong United',
                'composer' => 'Matt Crocker / Joel Houston / Salomon Ligthelm',
                'original_key' => 'D',
                'tempo' => 72,
                'time_signature' => '4/4',
                'lyrics_with_chords' => "[Verse 1]\nD                        A\nYou call me out upon the waters\n    Bm                    G\nThe great unknown where feet may fail\nD                           A\nAnd there I find You in the mystery\n   Bm                      G\nIn oceans deep my faith will stand\n\n[Chorus]\nD                       A\nAnd I will call upon Your name\n    Bm                    G\nAnd keep my eyes above the waves\n     D                          A\nWhen oceans rise my soul will rest in Your embrace\n   Bm             G\nFor I am Yours and You are mine\n\n[Bridge]\nBm         G\nSpirit lead me where my trust is without borders\nD              A\nLet me walk upon the waters\nBm         G\nWherever You would call me\nD                  A\nTake me deeper than my feet could ever wander",
                'notes' => 'Build slowly. Let the bridge breathe.',
            ],
            [
                'title' => 'Blessed Be Your Name',
                'artist' => 'Matt Redman',
                'composer' => 'Matt Redman / Beth Redman',
                'original_key' => 'A',
                'tempo' => 138,
                'time_signature' => '4/4',
                'lyrics_with_chords' => "[Verse 1]\nA                E\nBlessed be Your name\n              F#m\nIn the land that is plentiful\n              D\nWhere the streams of abundance flow\nA\nBlessed be Your name\n\n[Verse 2]\nA                E\nBlessed be Your name\n                  F#m\nWhen I'm found in the desert place\n             D\nThough I walk through the wilderness\nA\nBlessed be Your name\n\n[Chorus]\nA              E\nBlessed be Your name\n    F#m              D\nWhen the sun's shining down on me\nA              E\nWhen the world's all as it should be\n    F#m\nBlessed be Your name\n\n[Bridge]\n    D           A\nYou give and take away\n    D           A\nYou give and take away\nD               E\nMy heart will choose to say\nA\nLord blessed be Your name",
                'notes' => 'Upbeat praise song. Good opener.',
            ],
        ];

        $createdSongs = [];

        foreach ($songs as $data) {
            $createdSongs[] = Song::create([
                ...$data,
                'created_by' => $user->id,
                'is_active' => true,
            ]);
        }

        $setlist = Setlist::create([
            'created_by' => $user->id,
            'title' => 'Sunday Morning Service',
            'service_date' => now()->next('Sunday')->toDateString(),
            'theme' => 'Grace and Worship',
            'notes' => 'Sample setlist for display testing.',
            'status' => 'published',
        ]);

        // Add 3 songs — How Great Is Our God overridden to key of D
        $setlist->songs()->attach($createdSongs[0]->id, ['order' => 0]);
        $setlist->songs()->attach($createdSongs[1]->id, ['order' => 1, 'key_override' => 'D']);
        $setlist->songs()->attach($createdSongs[2]->id, ['order' => 2]);
    }
}
