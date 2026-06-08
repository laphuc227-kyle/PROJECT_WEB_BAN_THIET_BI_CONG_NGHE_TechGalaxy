<?php
declare(strict_types=1);

class CartController{
    private Cart $cartModel;
    private CartItem $cartItemModel;
    private Product $productModel;

    public function __construct()
    {
        $this->cartModel = new Cart();
        $this->cartItemModel = new CartItem();
        $this->productModel = new Product();
    }
}

