<?php
/**
 * DokuWiki Plugin disableactionsbygroup (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Hansson
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once DOKU_PLUGIN . 'action.php';

class action_plugin_disableactionsbygroup extends DokuWiki_Action_Plugin
{
    public function register(Doku_Event_Handler &$controller)
    {
        $controller->register_hook('AUTH_LOGIN_CHECK', 'AFTER', $this, 'handle_post_login');
        $controller->register_hook('MENU_ITEMS_ASSEMBLY', 'AFTER', $this, 'handle_login_button');
    }

    public function handle_post_login(Doku_Event &$event, $param)
    {
        // If authentication failed
        if (!$event->result) {
            // Handle settings for ALL users (non logged in)
            $this->disablebygroupids(array('ALL'));
            return;
        }

        global $USERINFO;
        // Handle settings for logged in users
        $this->disablebygroupids($USERINFO['grps']);
    }

    private function disablebygroupids($groupids)
    {
        global $conf;
        // Check denyactionsbygroup to see if the user is in any matching group
        $actionsbygroup = explode(';', $this->getConf('disableactionsbygroup'));
        foreach ($actionsbygroup as $groupandactions) {
            list($group, $action) = explode(":", $groupandactions);
            foreach ((array)$groupids as $membergroup) {
                if ($membergroup == $group) {
                    $conf['disableactions'] = $action;
                    break 2;
                }
            }
        }
    }

    /**
     * remove login button for guest
     */
    public function handle_login_button(&$event, $param)
    {
        global $INFO;
        if (empty($INFO["userinfo"])) {
            foreach ($event->data['items'] as $index => $oneItem) {
                if ($oneItem instanceof dokuwiki\Menu\Item\Login) {
                    unset($event->data['items'][$index]);
                }
            }
        }
    }
}

// vim:ts=4:sw=4:et:
