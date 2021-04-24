<?php

/*
 * Copyright (C) 2017 David Harrigan
 * Copyright (C) 2017 Deciso B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace OPNsense\Nebula\Api;

require_once 'plugins.inc.d/nebula.inc';

use OPNsense\Base\ApiMutableModelControllerBase;
use OPNsense\Base\UIModelGrid;
use OPNsense\Core\Backend;
use OPNsense\Core\Config;
use OPNsense\Nebula\Nebula;

class SettingsController extends ApiMutableModelControllerBase
{

    protected static $internalModelName = 'Nebula';
    protected static $internalModelClass = '\OPNsense\Nebula\Nebula';

    public function getAction()
    {
        $result = array();
        if ($this->request->isGet()) {
            $mdlNebula = $this->getModel();
            if (empty($mdlNebula->localconf->__toString())) {
                $mdlNebula->localconf = '{}';
            }
            $result = array("nebula" => $mdlNebula->getNodes());
        }
        return $result;
    }

    public function setAction()
    {
        $result = array("result" => "failed");
        if ($this->request->isPost() && $this->request->hasPost("nebula")) {
            $mdlNebula = $this->getModel();
            $mdlNebula->setNodes($this->request->getPost("nebula"));
            $mdlNebula->serializeToConfig();
            Config::getInstance()->save();
            $enabled = isEnabled($mdlNebula);
            $result["result"] = $this->toggleNebulaService($enabled);
        }
        return $result;
    }

    public function statusAction()
    {
        $mdlNebula = $this->getModel();
        $enabled = isEnabled($mdlNebula);

        $response = trim((new Backend())->configdRun('nebula status'));

        if (strpos($response, "not running") > 0) {
            if (isEnabled($mdlNebula)) {
                $status = "stopped";
            } else {
                $status = "disabled";
            }
        } elseif (strpos($response, "is running") > 0) {
            $status = "running";
        } elseif (!$enabled) {
            $status = "disabled";
        } else {
            $status = "unknown";
        }

        return array("result" => $status);
    }

    private function toggleNebulaService($enabled)
    {
        $backend = new Backend();
        $backend->configdRun("template reload OPNsense/nebula");
        $action = $enabled ? "start" : "stop";
        return trim($backend->configdRun("nebula $action"));
    }
}
