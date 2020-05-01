<?php
/* Copyright (c) 2020, VRAI Labs and/or its affiliates. All rights reserved.
 *
 * This software is licensed under the Apache License, Version 2.0 (the
 * "License") as published by the Apache Software Foundation.
 *
 * You may not use this file except in compliance with the License. You may
 * obtain a copy of the License at http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

namespace App;

class Utils
{
    public static $instance = null;

    public static function getInstance(): Utils
    {
        if (is_null(Utils::$instance)) {
            Utils::$instance = new Utils();
        }
        return Utils::$instance;
    }

    public function getSessionCount()
    {
        return $this->readFromFile("sessionFile");
    }

    public function getRefreshCount()
    {
        return $this->readFromFile("refreshFile");
    }

    public function incrementRefreshCount()
    {
        $r = $this->readFromFile("refreshFile");
        $this->writeToFile("refreshFile", $r + 1);
    }

    public function incrementSessionCount()
    {
        $s = $this->readFromFile("sessionFile");
        $this->writeToFile("sessionFile", $s + 1);
    }

    public function reset()
    {
        $this->writeToFile("refreshFile", 0);
        $this->writeToFile("sessionFile", 0);
    }

    private function writeToFile($fname, $val)
    {
        $myfile = fopen("$fname", "w");
        $txt = (string)$val;
        fwrite($myfile, $txt);
        fclose($myfile);
    }

    private function readFromFile($fname)
    {
        $myfile = fopen($fname, "r");
        try {
            return (int)(fread($myfile, filesize($fname)));
        } finally {
            fclose($myfile);
        }
    }
}