<?php

namespace Bouh\Paginator;

class Paginator
{

    use Immutable;

    /**
     * Immutable values :
     *  items
     *  totalItems
     *  pages
     *  currentPage
     *  previousPage
     *  nextPage
     *  firstPage
     *  lastPage
     */

    private $minimumPageAround = 2;
    private $itemsPerPage = null;
    private $urlCallable = null;
    private $totalCallable = null;
    private $sliceCallable = null;


    public function setMinimumPageAround($minimumPageAround)
    {
        if ($this->minimumPageAround !== null) {
            throw new \Exception('minimumPageAround is immutable');
        }

        $this->minimumPageAround = (int) $minimumPageAround;
    }


    public function setItemsPerPage($itemsPerPage)
    {
        if ($this->itemsPerPage !== null) {
            throw new \Exception('itemsPerPage is immutable');
        }

        $this->itemsPerPage = (int) $itemsPerPage;
    }


    public function setUrlCallable(callable $urlCallable)
    {
        if ($this->urlCallable !== null) {
            throw new \Exception('urlCallable is immutable');
        }

        $this->urlCallable = $urlCallable;
    }


    public function url($page)
    {
        $urlCallable = $this->urlCallable;
        return $urlCallable($page);
    }


    public function setTotalCallable(callable $totalCallable)
    {
        if ($this->totalCallable !== null) {
            throw new \Exception('totalCallable is immutable');
        }

        $this->totalCallable = $totalCallable;
    }


    public function setSliceCallable(callable $sliceCallable)
    {
        if ($this->sliceCallable !== null) {
            throw new \Exception('sliceCallable is immutable');
        }

        $this->sliceCallable = $sliceCallable;
    }


    public function paginate($currentPage)
    {
        if ($this->values['currentPage'] !== null) {
            throw new \Exception('Paginate already done with page ' . $this->page);
        }

        $this->values['currentPage'] = (int) $currentPage;

        $extraData = 0;
        if ($this->totalCallable === null) {
            $extraData = $this->itemsPerPage * $this->minimumPageAround + 1;
        }

        $sliceCallable = $this->sliceCallable;
        $offset = ($this->currentPage - 1) * $this->itemsPerPage;
        $items = $sliceCallable($offset, $this->itemsPerPage + $extraData);

        if ($this->totalCallable === null) {
            $count = 0;
            $this->values['items'] = array();
            foreach ($items as $key => $item) {
                $count++;

                if ($count > $this->itemsPerPage) {
                    continue;
                }

                $this->values['items'][$key] = $item;
            }

            if ($count === 0) {
                $maxPage = $this->currentPage;
            } else {
                $maxPage = (int) ceil(($offset + $count) / $this->itemsPerPage);
            }
        } else {
            $totalCallable = $this->totalCallable;
            $this->values['totalItems'] = $totalCallable();
            if ($items instanceof \Traversable) {
                $this->values['items'] = iterator_to_array($items);
            } else {
                $this->values['items'] = $items;
            }
            $maxPage = (int) ceil($this->values['totalItems'] / $this->itemsPerPage);
            $this->values['lastPage'] = $maxPage;
        }

        $minRange = max(1, $this->currentPage - $this->minimumPageAround);
        $maxRange = min($maxPage, $this->currentPage + $this->minimumPageAround);

        if ($maxRange - $minRange < $this->minimumPageAround * 2) {
            if ($this->currentPage + $this->minimumPageAround <= $maxRange) {
                $maxRange = min($maxPage, $this->minimumPageAround * 2 + 1);
            } else {
                $minRange = max(1, $maxPage - $this->minimumPageAround * 2);
            }
        }

        $this->values['pages'] = range($minRange, $maxRange);
        $this->values['firstPage'] = 1;

        if ($this->currentPage > 1) {
            $this->values['previousPage'] = $this->currentPage - 1;
        }

        $this->values['nextPage'] = min($this->currentPage + 1, $maxPage);

        if ($this->values['nextPage'] === $this->currentPage) {
            unset($this->values['nextPage']);
        }
    }
}
