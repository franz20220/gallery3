<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
class core_installer {
  public static function install() {
    $db = Database::instance();
    $version = 0;
    try {
      $version = module::get_version("core");
    } catch (Exception $e) {
      if ($e->getCode() != E_DATABASE_ERROR) {
        Kohana::log("error", $e);
        throw $e;
      }
    }

    if ($version == 0) {
      $db->query("CREATE TABLE `access_caches` (
                   `id` int(9) NOT NULL auto_increment,
                   `item_id` int(9),
                   PRIMARY KEY (`id`))
                 ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      $db->query("CREATE TABLE `access_intents` (
                   `id` int(9) NOT NULL auto_increment,
                   `item_id` int(9),
                   PRIMARY KEY (`id`))
                 ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      $db->query("CREATE TABLE `items` (
                   `description` char(255) default NULL,
                   `height` int(9) default NULL,
                   `id` int(9) NOT NULL auto_increment,
                   `left` int(9) NOT NULL,
                   `level` int(9) NOT NULL,
                   `mime_type` char(64) default NULL,
                   `name` char(255) default NULL,
                   `owner_id` int(9) default NULL,
                   `parent_id` int(9) NOT NULL,
                   `resize_height` int(9) default NULL,
                   `resize_width` int(9) default NULL,
                   `right` int(9) NOT NULL,
                   `thumb_height` int(9) default NULL,
                   `thumb_width` int(9) default NULL,
                   `title` char(255) default NULL,
                   `type` char(32) NOT NULL,
                   `width` int(9) default NULL,
                   PRIMARY KEY (`id`),
                   KEY `parent_id` (`parent_id`),
                   KEY `type` (`type`))
                 ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      $db->query("CREATE TABLE `modules` (
                   `id` int(9) NOT NULL auto_increment,
                   `name` char(255) default NULL,
                   `version` int(9) default NULL,
                   PRIMARY KEY (`id`),
                   UNIQUE KEY(`name`))
                 ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      $db->query("CREATE TABLE `permissions` (
                   `id` int(9) NOT NULL auto_increment,
                   `name` char(255) default NULL,
                   `version` int(9) default NULL,
                   PRIMARY KEY (`id`),
                   UNIQUE KEY(`name`))
                 ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      $db->query("CREATE TABLE `sessions` (
                  `session_id` varchar(127) NOT NULL,
                  `last_activity` int(10) UNSIGNED NOT NULL,
                  `data` text NOT NULL,
                  PRIMARY KEY (`session_id`))
                 ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      $db->query("CREATE TABLE `vars` (
                   `id` int(9) NOT NULL auto_increment,
                   `module_id` int(9),
                   `name` char(255) NOT NULL,
                   `value` text,
                   PRIMARY KEY (`id`),
                   UNIQUE KEY(`module_id`, `name`))
                 ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      foreach (array("albums", "resizes", "thumbs", "uploads") as $dir) {
        @mkdir(VARPATH . $dir);
      }

      access::register_permission("view");
      access::register_permission("edit");

      $root = ORM::factory("item");
      $root->type = 'album';
      $root->title = "Gallery";
      $root->description = "Welcome to your Gallery3";
      $root->left = 1;
      $root->right = 2;
      $root->parent_id = 0;
      $root->level = 1;
      $root->set_thumb(DOCROOT . "core/tests/test.jpg", 200, 200);
      $root->save();
      access::add_item($root);

      // Save this before setting vars so that module id is set
      module::set_version("core", 1);

      module::set_var("core", "active_theme", "default");
      module::set_var("core", "active_admin_theme", "admin_default");
      module::set_var("core", "page_size", 9);
      module::set_var("core", "thumb_size", 200);
      module::set_var("core", "resize_size", 640);
    }
  }

  public static function uninstall() {
    $db = Database::instance();
    $db->query("DROP TABLE IF EXISTS `access_caches`;");
    $db->query("DROP TABLE IF EXISTS `access_intents`;");
    $db->query("DROP TABLE IF EXISTS `permissions`;");
    $db->query("DROP TABLE IF EXISTS `items`;");
    $db->query("DROP TABLE IF EXISTS `modules`;");
    $db->query("DROP TABLE IF EXISTS `vars`;");
    system("/bin/rm -rf " . VARPATH . "albums");
    system("/bin/rm -rf " . VARPATH . "resizes");
    system("/bin/rm -rf " . VARPATH . "thumbs");
    system("/bin/rm -rf " . VARPATH . "uploads");
  }
}
