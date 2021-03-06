<?php
/*
 * AUTOGENERATED CODE - DO NOT EDIT!
 *
 * This base class was generated by the Trellis library and
 * must not be modified manually. Any modifications to this
 * file will be lost upon triggering the next automatic
 * class generation.
 *
 * If you are looking for a place to alter the behaviour of
 * 'User' entities please see the skeleton
 * class 'User'. Modifications to the skeleton
 * file will prevail any subsequent class generation runs.
 *
 * To define new attributes or adjust existing attributes and their
 * default options modify the schema definition file of
 * the 'User' type.
 *
 * @see https://github.com/honeybee/trellis
 */

namespace Honeybee\SystemAccount\User\Model\Aggregate\Base;

use Honeybee\Model\Aggregate\AggregateRoot;

/**
 * Serves as the base class to the 'User' entity skeleton.
 *
 * This class contains definitions for attributes and their options available
 * on 'User' instances. Modifications to getters and setters
 * as well as new additional helper methods should not be placed in here,
 * but be implemented within the skeleton class 'User'.
 *
 * Attributes can have default and null values set via their options. The keys
 * are named 'default_value' and 'null_value' respectively.
 *
 * This class extends the 'AggregateRoot' class to get the change events and
 * validation handling behaviour of that class.
 */
abstract class User extends AggregateRoot
{
    /**
     * Returns the current value of the 'username' attribute on this
     * 'User' entity. The 'default_value' option set for
     * this attribute is returned if no value was set. If neither a value nor
     * default value was set the 'null_value' option value is returned.
     *
     * @return mixed Value or default value of attribute 'username'. Null value otherwise (defaults to NULL).
     */
    public function getUsername()
    {
        return $this->getValue('username');
    }

    /**
     * Returns the current value of the 'email' attribute on this
     * 'User' entity. The 'default_value' option set for
     * this attribute is returned if no value was set. If neither a value nor
     * default value was set the 'null_value' option value is returned.
     *
     * @return mixed Value or default value of attribute 'email'. Null value otherwise (defaults to NULL).
     */
    public function getEmail()
    {
        return $this->getValue('email');
    }

    /**
     * Returns the current value of the 'role' attribute on this
     * 'User' entity. The 'default_value' option set for
     * this attribute is returned if no value was set. If neither a value nor
     * default value was set the 'null_value' option value is returned.
     *
     * @return mixed Value or default value of attribute 'role'. Null value otherwise (defaults to NULL).
     */
    public function getRole()
    {
        return $this->getValue('role');
    }

    /**
     * Returns the current value of the 'firstname' attribute on this
     * 'User' entity. The 'default_value' option set for
     * this attribute is returned if no value was set. If neither a value nor
     * default value was set the 'null_value' option value is returned.
     *
     * @return mixed Value or default value of attribute 'firstname'. Null value otherwise (defaults to NULL).
     */
    public function getFirstname()
    {
        return $this->getValue('firstname');
    }

    /**
     * Returns the current value of the 'lastname' attribute on this
     * 'User' entity. The 'default_value' option set for
     * this attribute is returned if no value was set. If neither a value nor
     * default value was set the 'null_value' option value is returned.
     *
     * @return mixed Value or default value of attribute 'lastname'. Null value otherwise (defaults to NULL).
     */
    public function getLastname()
    {
        return $this->getValue('lastname');
    }

    /**
     * Returns the current value of the 'password_hash' attribute on this
     * 'User' entity. The 'default_value' option set for
     * this attribute is returned if no value was set. If neither a value nor
     * default value was set the 'null_value' option value is returned.
     *
     * @return mixed Value or default value of attribute 'password_hash'. Null value otherwise (defaults to NULL).
     */
    public function getPasswordHash()
    {
        return $this->getValue('password_hash');
    }

    /**
     * Returns the current value of the 'background_images' attribute on this
     * 'User' entity. The 'default_value' option set for
     * this attribute is returned if no value was set. If neither a value nor
     * default value was set the 'null_value' option value is returned.
     *
     * @return mixed Value or default value of attribute 'background_images'. Null value otherwise (defaults to NULL).
     */
    public function getBackgroundImages()
    {
        return $this->getValue('background_images');
    }

    /**
     * Returns the current value of the 'auth_token' attribute on this
     * 'User' entity. The 'default_value' option set for
     * this attribute is returned if no value was set. If neither a value nor
     * default value was set the 'null_value' option value is returned.
     *
     * @return mixed Value or default value of attribute 'auth_token'. Null value otherwise (defaults to NULL).
     */
    public function getAuthToken()
    {
        return $this->getValue('auth_token');
    }

    /**
     * Returns the current value of the 'token_expire_date' attribute on this
     * 'User' entity. The 'default_value' option set for
     * this attribute is returned if no value was set. If neither a value nor
     * default value was set the 'null_value' option value is returned.
     *
     * @return mixed Value or default value of attribute 'token_expire_date'. Null value otherwise (defaults to NULL).
     */
    public function getTokenExpireDate()
    {
        return $this->getValue('token_expire_date');
    }
}
