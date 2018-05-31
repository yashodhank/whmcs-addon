<h3>{$MGLANG->T('reissueTwoTitle')}</h3>
{$MGLANG->T('reissueTwoSubTitle')}

{assign var=val value=0}

<div class="row">
    <div class="col-sm-12">
        <form method="POST" action="{$smarty.server.REQUEST_URI}" class="form-horizontal">
            <input type="hidden" name="stepTwoForm" value="tak">
            <input type="hidden" name="webservertype" value="{$smarty.post.webservertype}">
            <input type="hidden" name="csr" value="{$smarty.post.csr}">
            <input type="hidden" name="sans_domains" value="{$smarty.post.sans_domains}">
            <div class="loading">
                Loading...
            </div>
            <!--{foreach from=$approvalEmails key=domain item=domainApprovalEmail}
                <h3>{$domain}</h3>
                <div class="form-group">
                    {assign var=firstRadio value=0}
                    {foreach from=$domainApprovalEmail item=approvalEmail}
                        <div class="radio">
                            {if $val === 0}
                                <label><input {if $firstRadio === 0}checked{/if} type="radio" name="approveremail" value="{$approvalEmail}">{$approvalEmail}</label>
                                {else}
                                <label><input {if $firstRadio === 0}checked{/if} type="radio" name="approveremails[{$domain}]" value="{$approvalEmail}">{$approvalEmail}</label>
                                {/if}
                        </div>
                        {assign var=firstRadio value=$firstRadio+1}
                    {/foreach}
                </div>
                {assign var=val value=$val+1}
            {/foreach}-->
            <div class="col-sm-12 text-center">
                <input id="reissueCertificateButton" type="submit" value="{$MGLANG->T('reissueTwoContinue')}" class="btn btn-primary">
            </div>
        </form>
    </div>
</div>
            
 <script type="text/javascript">
    $(document).ready(function () {        
        //var fillVars = JSON.parse('{$fillVars}');
        var brand = JSON.parse('{$brand}');        
        var onlyEmailValidationFoBrands = ['geotrust','thawte','rapidssl','symantec'];
        
        /*var mainDomainDcvMethod = '';
        for (var i = 0; i < fillVars.length; i++) {
             if(fillVars[i].name === "fields[dcv_method]") {
                mainDomainDcvMethod = fillVars[i].value;
             }
        }*/
        function getSelectHtml(value, checked) {
            if (checked) {
                var ck = ' selected';
            } else {
                var ck = '';
            }
            return '<option value="' + value + '"' + ck + '>' + value + '</option>'
        }
        function getRowHtml(title, methods, emails) {
            return '<tr><td>' + title + '</td><td>' + methods + '</td><td>' + emails + '</td></tr>';
        }
        function getNameForSelectMethod(x, domain) {
            if (x === 0) {
                return 'name="dcvmethodMainDomain"';
            }
            return 'name="dcvmethod[' + domain + ']"';
        }
        function getNameForSelectEmail(x, domain) {
            if (x === 0) {
                return 'name="approveremail"';
            }
            return 'name="approveremails[' + domain + ']"';
        }
        function getTable(tableBegin, tableEnd, body) {
            return tableBegin + body + tableEnd;
        }
        function replaceRadioInputs(sanEmails) {
            var template = $('.loading'),
                    selectEmailHtml = '',
                    fullHtml = '',
                    partHtml = '',
                    tableBegin = '<div class="col-sm-10 col-sm-offset-1"><table id="selectDcvMethodsTable" class="table"><thead><tr><th>'+'{$MGLANG->T('stepTwoTableLabelDomain')}'+'</th><th>'+'{$MGLANG->T('stepTwoTableLabelDcvMethod')}'+'</th><th>'+'{$MGLANG->T('stepTwoTableLabelEmail')}'+'</th></tr></thead>',
                    tableEnd = '</table></div>',                    
                    selectDcvMethod = '',
                    selectBegin = '<div class="form-group"><select style="width:80%;" type="text" name="selectName" class="form-control">',
                    selectEnd = '</select></div>',
                    x = 0;
            
            //for template control
            if(template.find('.panel').length > 0) {
                template = $('input[value="loading..."]').closest('.panel-body').find('div');
            }
            
            if(jQuery.inArray(brand, onlyEmailValidationFoBrands) < 0){
                selectDcvMethod = '<div class="form-group"><select style="width:65%;" type="text" name="selectName" class="form-control"><option value="EMAIL">'+'{$MGLANG->T('dropdownDcvMethodEmail')}'+'</option><option value="HTTP">'+'{$MGLANG->T('dropdownDcvMethodHttp')}'+'</option><option value="HTTPS">'+'{$MGLANG->T('dropdownDcvMethodHttps')}'+'</option><option value="DNS">'+'{$MGLANG->T('dropdownDcvMethodDns')}'+'</option></select>' ;
            } else { 
                selectDcvMethod = '<div class="form-group"><select style="width:65%;" type="text" name="selectName" class="form-control"><option value="EMAIL">'+'{$MGLANG->T('dropdownDcvMethodEmail')}'+'</option></select>';
            }
                   
            template.hide();
            //$('input[value="loading..."]').remove();
            
            $.each(sanEmails, function (domain, emails) {
                
                partHtml = partHtml + selectDcvMethod.replace('name="selectName"', getNameForSelectMethod(x, domain));
                selectEmailHtml = selectBegin.replace('name="selectName"', getNameForSelectEmail(x, domain));
                for (var i = 0; i < emails.length; i++) {
                    selectEmailHtml = selectEmailHtml +  getSelectHtml(emails[i], i === 0);
                }
                selectEmailHtml = selectEmailHtml + selectEnd;
                fullHtml = fullHtml + getRowHtml(domain, partHtml, selectEmailHtml);                
                
                partHtml = '';
                x++;
            });
            template.before(getTable(tableBegin, tableEnd, fullHtml));
            template.remove();
        }
        
        replaceRadioInputs(JSON.parse('{$approvalEmails}'));
        $('select[name^="dcvmethod"]').change( function (){
            var method = this.value;
            var selectName = this.name;
            var domain = selectName.replace('dcvmethod', '');
            if(domain === 'MainDomain') {
                if(method !== 'EMAIL') {
                    $('select[name="approveremail"]').addClass('hidden');
                } else {                    
                    $('select[name="approveremail"]').removeClass('hidden');   
                }
            } else {
                if(method !== 'EMAIL') {
                    $('select[name="approveremails'+domain+'"]').addClass('hidden');   
                } else {
                    $('select[name="approveremails'+domain+'"]').removeClass('hidden');   
                }                 
            }
        });
        if(jQuery.inArray(brand, onlyEmailValidationFoBrands) >= 0){
            $('select[name^="approveremails"]').closest('tr').prop('hidden', true);
        }
        
        {literal}//var sanEmails = JSON.parse('{\"friz.pl\":[\"admin@friz.pl\",\"administrator@friz.pl\"],\"kot.pl\":[\"admin@kot.pl\",\"administrator@kot.pl\"]}');{/literal} 
    });
</script>
