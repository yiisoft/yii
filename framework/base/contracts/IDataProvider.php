<?php

declare(strict_types=1);

/**
 * IDataProvider is the interface that must be implemented by data provider classes.
 *
 * Data providers are components that can feed data for widgets such as data grid, data list.
 * Besides providing data, they also support pagination and sorting.
 *
 * @author  Qiang Xue <qiang.xue@gmail.com>
 * @package system.web
 * @since   1.1
 */
interface IDataProvider
{
    /**
     * @return string the unique ID that identifies the data provider from other data providers.
     */
    public function getId();

    /**
     * Returns the number of data items in the current page.
     * This is equivalent to <code>count($provider->getData())</code>.
     * When {@link pagination} is set false, this returns the same value as {@link totalItemCount}.
     *
     * @param boolean $refresh whether the number of data items should be re-calculated.
     *
     * @return integer the number of data items in the current page.
     */
    public function getItemCount($refresh = false);

    /**
     * Returns the total number of data items.
     * When {@link pagination} is set false, this returns the same value as {@link itemCount}.
     *
     * @param boolean $refresh whether the total number of data items should be re-calculated.
     *
     * @return integer total number of possible data items.
     */
    public function getTotalItemCount($refresh = false);

    /**
     * Returns the data items currently available.
     *
     * @param boolean $refresh whether the data should be re-fetched from persistent storage.
     *
     * @return array the list of data items currently available in this data provider.
     */
    public function getData($refresh = false);

    /**
     * Returns the key values associated with the data items.
     *
     * @param boolean $refresh whether the keys should be re-calculated.
     *
     * @return array the list of key values corresponding to {@link data}. Each data item in {@link data}
     * is uniquely identified by the corresponding key value in this array.
     */
    public function getKeys($refresh = false);

    /**
     * @return CSort the sorting object. If this is false, it means the sorting is disabled.
     */
    public function getSort();

    /**
     * @return CPagination the pagination object. If this is false, it means the pagination is disabled.
     */
    public function getPagination();
}