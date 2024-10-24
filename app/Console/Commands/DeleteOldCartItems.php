<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CartModel;

class DeleteOldCartItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carts:deleteOldItems';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete items in the cart older than 2 years';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $twoYearsAgo = now()->subYears(2);
        $deletedCount = CartModel::where('updated_at', '<=', $twoYearsAgo)->delete();

        $this->info("오래된 장바구니 아이템 $deletedCount 개 삭제 완료.");
    }
}
