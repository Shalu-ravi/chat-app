<?php
    // Declare project namespace.s
    namespace ChatApp;

    /**
     * Class used to represent an abstract DataObject, such as a User or a
     * Message. Provides a function to save the data in the object back to the
     * database, and a way to access properties from a retrieved object.
     */ 
    class DataObject {
        // Private Object-relational Mapping (ORM) object instance. When
        // interacting with this object, use the names for the properties from
        // the db columns for which this object interacts with.
        protected $orm;
        
        /**
         * Writes the User object's existing object data to the database.
         * Returns true if save succeeded, false otherwise.
         * @return boolean
         */
        public function save() {
            if ($this->orm) {
                $this->orm->save();
                return true;
            }
            return false;
        }

        /**
         * Private constructor defined to prevent instantiation of this class
         * directly.
         */
        private function __construct() {}

        /**
         * Magic method for accessing the elements of the private $orm instance
         * as properties of the data object.
         * @param string $property The accessed property's name 
         * @return mixed
         */
        public function __get($property) {
            // Check if the property is directly on the instance.
            if (isset($this->$property)) {
                return $this->$property;
            // Check if the property is within the $orm instance.
            } elseif ($this->orm && isset($this->orm->$property)) {
                return $this->orm->$property;
            }
            return null;
        }

        /**
         * Magic method for setting elements of the private $orm instance as
         * properties of the data object.
         * @param string $property The property's name 
         * @param mixed $value The property's value
         * @return $this
         */
        public function __set($property, $value) {
            // Check if the property is directly on the instance.
            if (property_exists($this, $property)) {
                $this->$property = $value;
            // Check if orm is null; if it's not, set the property.
            } elseif ($this->orm) {
                $this->orm->$property = $value;
            }
            return $this;
        }
    }
?>