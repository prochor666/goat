<?php
namespace GoatCore\Base;

/**
* Store - DI repository
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/
class Store {

    protected $entries;

    /**
    * Initialize store
    * @return void
    */
    public function __construct(array $entries = [])
    {
        $this->entries = $entries;
        return $this;
    }


    /**
    * Gets and sets store entry
    * @param object|string $entry
    * @return object|bool
    */
    public function entry($entry = false)
    {
        if (is_object($entry)) {
            $entryKey = get_class($entry);
            $this->entries[$entryKey] = $entry;
            return $this->entries[$entryKey];
        }

        if (is_string('entry') && ark($this->entries, $entry) && is_object($this->entries[$entry])) {
            return $this->entries[$entry];
        }

        return false;
    }


    /**
    * Gets all entries
    * @return array
    */
    public function getEntries(): array
    {
        return $this->entries;
    }


    /**
    * Remove store entry
    * @param string $entry
    * @return void
    */
    public function remove($entry): void
    {
        if (is_string('entry') && ark($this->entries, $entry)) {
            unset($this->entries[$entry]);
        }
    }
}
