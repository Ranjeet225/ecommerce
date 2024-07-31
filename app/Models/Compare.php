<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compare extends Model
{
    public $items = null;
    // public function __construct($oldCompare)
    // {
    //     if ($oldCompare) {
    //         $this->items = $oldCompare->items;
    //     }
    // }

    public static function restoreCart($oldCompare)
    {
        $cart = new static();
    
        if ($oldCompare) {
            $cart->items = $oldCompare->items;
           
        }
    
        return $cart;
    }

    public function add($item, $id) {
        $storedItem = ['ck' => 0, 'item' => $item];
        if ($this->items) {
            if (array_key_exists($id, $this->items)) {
                $storedItem = $this->items[$id];
            	$storedItem['ck'] = 1;
            }
        }
        $this->items[$id] = $storedItem;
    }
    public function removeItem($id) {
        unset($this->items[$id]);
    }
}
