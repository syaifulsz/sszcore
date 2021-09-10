<?php

namespace sszcore\components\View\FontAwesome;

/**
 * Class FontAwesome
 * @package sszcore\components\View\FontAwesome
 * @since 0.2.2
 */
class FontAwesome
{
    const FAR_FA_LONG_ARROW_ALT_RIGHT = 'far fa-long-arrow-alt-right';
    const FAR_FA_LONG_ARROW_ALT_LEFT  = 'far fa-long-arrow-alt-left';
    const FAS_FA_PLUS_CIRCLE          = 'fas fa-plus-circle';
    const FAS_FA_ELLIPSIS_CIRCLE      = 'fas fa-ellipsis-v';
    const FAR_FA_ELLIPSIS_CIRCLE      = 'far fa-ellipsis-v';
    const FAL_FA_ELLIPSIS_CIRCLE      = 'fal fa-ellipsis-v';
    const FAS_FA_USER_UNLOCK          = 'fas fa-user-unlock';
    const FAS_FA_MASK                 = 'fas fa-mask';
    const FAS_FA_GIFTS                = 'fas fa-gifts';
    const FAS_FA_USERS                = 'fas fa-users';
    const FAS_FA_COPY                 = 'fas fa-copy';
    const FAS_FA_EYE                  = 'fas fa-eye';
    const FAS_FA_USER_NINJA           = 'fas fa-user-ninja';
    const FAS_FA_MONEY_CHECK_ALT      = 'fas fa-money-check-alt';
    const FAS_FA_USER_TIE             = 'fas fa-user-tie';
    const FAS_FA_COG                  = 'fas fa-cog';
    const FAS_FA_FILE_DOWNLOAD        = 'fas fa-file-download';
    const FAS_FA_BOX_FULL             = 'fas fa-box-full';
    const FAL_FA_BOX_FULL             = 'fal fa-box-full';
    const FAS_FA_STORE                = 'fas fa-store';
    const FAS_FA_SHOPPING_CART        = 'fas fa-shopping-cart';
    const FAS_FA_HEART                = 'fas fa-heart';
    const FAS_FA_SITEMAP              = 'fas fa-sitemap';
    const FAS_FA_ADDRESS_CARD         = 'fas fa-address-card';
    const FAS_FA_CREDIT_CARD_FRONT    = 'fas fa-credit-card-front';
    const FAS_FA_CREDIT_CARD_BLANK    = 'fas fa-credit-card-blank';

    const ECOMMERCE_BUY_BOX_ITEMS     = self::FAS_FA_SHOPPING_CART;
    const ECOMMERCE_BUY_LOOSE_ITEMS   = self::FAS_FA_SHOPPING_CART;
    const ECOMMERCE_MY_OUTLET         = self::FAS_FA_STORE;
    const AGENT_ID                    = self::FAS_FA_ADDRESS_CARD;
    const AGENT_LEVEL                 = self::FAS_FA_CREDIT_CARD_BLANK;

    /**
     * @param string $fa
     * @param string $class
     * @return string
     */
    public static function html( string $fa, string $class = '' )
    {
        return "<i class=\"{$fa} {$class}\"></i>";
    }
}