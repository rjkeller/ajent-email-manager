<?php
namespace AjentServers\ExternalMailServerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle as BaseBundle;

/**
 * Provides a bunch of functions for someone to synchronize their external
 * email account with their Ajent email account.
 * 
 * The idea here was to copy consumer emails from a users' external account
 * to their Ajent account, and then delete that message from their external
 * account.
 */
class ExternalMailServerBundle extends BaseBundle
{
}
