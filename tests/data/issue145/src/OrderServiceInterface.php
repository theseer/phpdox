<?php
namespace Globetrotter\API\Services\Order\V1;
use Globetrotter\API\Services\ServiceInterface;

/**
 * Possible progress states:
 * 0001 - foo
 * 0002 - bar
 */
interface OrderServiceInterface extends ServiceInterface
{

    /**
     * Returns the currently open orders of the user.
     *
     * Returns:
     * {
     *     "messageCode": null,
     *     "page": 1, // current result page
     *     "entriesPerPage": 10, // entries per page
     *     "entries": 4, // total entries found
     *     "orders": [
     *         {
     *             "id": "0000916122",
     *             "orderDetails": {
     *                 "date": "28.04.2014",
     *                 "progress": "0001", // for possible progress states see interface docs!
     *                 "amount":500, // in Euro-Cent: 500 = 5,00 Euro
     *                 "currency": "EUR",
     *                 "state": "Bestellung eingegangen",
     *                 "download": [
     *                     {
     *                         "Date": "28.04.2014",
     *                         "Url": "...",
     *                         "DocumentType": "Download",
     *                         "MessageType": "n\/A"
     *                     },
     *                     { ... }
     *                 ]
     *             }
     *         },
     *         { ... }
     *     ]
     * }
     *
     * @param int $page
     * @param int $entriesPerPage
     * @return array
     */
    public function getOpenOrders($page, $entriesPerPage);

    /**
     * Returns the closed orders of the user.
     *
     * @param int $page
     * @param int $entriesPerPage
     * @return array
     */
    public function getClosedOrders($page, $entriesPerPage);

}
