<?php

use Illuminate\Database\Seeder;

class WorkiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $avatars = ['avatar7.png', 'avatar8.png', 'avatar9.png', 'avatar10.png', 'avatar6.png', 'avatar5.jpg', 'avatar4.jpg', 'avatar3.jpg', 'avatar2.jpg', 'avatar1.jpeg'];
        factory(Modules\Users\Entities\User::class, 100)->create()->each(function ($u) use ($avatars) {
            $faker = Faker\Factory::create('en_US');
            $u->profile()->update([
                'avatar' => array_random($avatars),
                'company' => rand(0, 10),
                'job_title' => $faker->jobTitle,
                'use_gravatar' => 0,
                'hourly_rate' => $faker->numberBetween(3, 20),
                'skype' => $faker->unique()->userName,
                'twitter' => $faker->unique()->userName,
            ]);
        });
        factory(Modules\Clients\Entities\Client::class, 100)->create();
        factory(Modules\Knowledgebase\Entities\Knowledgebase::class, 20)->create()->each(function ($article) {
            $article->comments()->saveMany(factory(Modules\Comments\Entities\Comment::class, 5)->make());
        });
        factory(Modules\Deals\Entities\Deal::class, 50)->create();
        factory(Modules\Contracts\Entities\Contract::class, 10)->create();
        

        factory(Modules\Invoices\Entities\Invoice::class, 80)->create()->each(function ($invoice) {
            $invoice->items()->saveMany(factory(Modules\Items\Entities\Item::class, 5)->make());
        });
        factory(Modules\Estimates\Entities\Estimate::class, 80)->create()->each(function ($estimate) {
            $estimate->items()->saveMany(factory(Modules\Items\Entities\Item::class, 5)->make());
        });
        factory(Modules\Creditnotes\Entities\CreditNote::class, 10)->create()->each(function ($credit) {
            $credit->items()->saveMany(factory(Modules\Items\Entities\Item::class, 5)->make());
        });
        factory(Modules\Leads\Entities\Lead::class, 50)->create();
        factory(Modules\Payments\Entities\Payment::class, 10)->create();
        factory(Modules\Projects\Entities\Project::class, 30)->create()->each(function ($project) {
            $project->assignees()->saveMany(factory(Modules\Teams\Entities\Assignment::class, rand(1, 4))->make());
            $project->tasks()->saveMany(factory(Modules\Tasks\Entities\Task::class, 10)->create()->each(function ($task) {
                $task->assignees()->saveMany(factory(Modules\Teams\Entities\Assignment::class, rand(1, 2))->make());
            }));
            $project->milestones()->saveMany(factory(Modules\Milestones\Entities\Milestone::class, 3)->make());
            $project->comments()->saveMany(factory(Modules\Comments\Entities\Comment::class, rand(1, 3))->make());
        });
        factory(Modules\Expenses\Entities\Expense::class, 10)->create()->each(function ($expense) {
            $expense->comments()->saveMany(factory(Modules\Comments\Entities\Comment::class, 5)->make());
        });
        
        factory(Modules\Tickets\Entities\Ticket::class, 50)->create()->each(function ($ticket) {
            $ticket->comments()->saveMany(factory(Modules\Comments\Entities\Comment::class, 3)->make());
        });
    }
}
