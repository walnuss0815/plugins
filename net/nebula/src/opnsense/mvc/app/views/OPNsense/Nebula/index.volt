{#

OPNsense® is Copyright © 2014 – 2017 by Deciso B.V.
Copyright (C) 2017 David Harrigan

All rights reserved.

Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

1.  Redistributions of source code must retain the above copyright notice,
    this list of conditions and the following disclaimer.

2.  Redistributions in binary form must reproduce the above copyright notice,
    this list of conditions and the following disclaimer in the documentation
    and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED “AS IS” AND ANY EXPRESS OR IMPLIED WARRANTIES,
INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
POSSIBILITY OF SUCH DAMAGE.

#}
<script>

    $(document).ready(function() {

        var nebulaSettings = {'settings': '/api/nebula/settings/get'};

        mapDataToFormUI(nebulaSettings).done(function(data) {
            formatTokenizersUI();
            $('select').selectpicker('refresh');
        });

        ajaxGet(url="/api/nebula/settings/status", sendData={}, callback=function(data, status) {
            updateServiceStatusUI(data['result']);
            toggleNetworksTab(data['result']);
        });

        $("#btn_save_settings").click(function() {
            $("#settings_progress").addClass("fa fa-spinner fa-pulse");
            saveFormToEndpoint(url="/api/nebula/settings/set", formid="settings", callback_ok=function(data, status) {
                ajaxGet(url="/api/nebula/settings/status", sendData={}, callback=function(data, status) {
                    updateServiceStatusUI(data['result']);
                    toggleNetworksTab(data['result']);
                });
                $("#settings_progress").removeClass("fa fa-spinner fa-pulse");
            });
        });
    });

</script>

<ul class="nav nav-tabs" data-tabs="tabs" id="maintabs">
    <li id="ztSettings" class="active"><a data-toggle="tab" href="#settings">{{ lang._('Settings') }}</a></li>
</ul>

<div class="tab-content content-box tab-content">
    <div id="settings" class="tab-pane fade in active">
        <div class="content-box">
            {{ partial("layout_partials/base_form", ['fields': settingsForm, 'id': 'settings', 'apply_btn_id': 'btn_save_settings']) }}
        </div>
    </div>
</div>
