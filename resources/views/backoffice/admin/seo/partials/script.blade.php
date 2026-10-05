@push('js')
<script>
$(function () {
    $.ajaxSetup({headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}});

    const $page = $('#seo-manager');
    const $wrapper = $('#content-wrapper');
    const $modal = $('#ajaxModal');
    const $modalBody = $('#modal-body');
    let searchTimer = null;

    const toast = (message, type='success') => window.showAlert
        ? window.showAlert(message, type)
        : Promise.resolve();

    const confirmAction = (title, text, button='Yes, continue') => window.showConfirm
        ? window.showConfirm({title, text, confirmButtonText:button})
        : Promise.resolve({isConfirmed:window.confirm(text)});

    function requestError(xhr, fallback='Request failed.') {
        const message = xhr.responseJSON?.message || fallback;
        toast(message, 'error');
    }

    function destroyEditors() {
        if (!window.tinymce || !$modalBody.length) return;
        $modalBody.find('textarea.tinymce-editor').each(function () {
            const editor = this.id ? window.tinymce.get(this.id) : null;
            if (editor) editor.remove();
            delete this.dataset.tinymceInitialized;
        });
    }

    function filterUrl() {
        const $form = $('#filterForm');
        const query = $form.serialize();
        return query ? $form.attr('action') + '?' + query : $form.attr('action');
    }

    function loadData(url=null, push=true) {
        const requestUrl = url || filterUrl();
        $wrapper.addClass('loading');
        $.get(requestUrl).done(function (res) {
            $wrapper.html(res.html);
            $('#checkAll').prop('checked', false);
            if (push && history.replaceState) history.replaceState({}, '', requestUrl);
        }).fail(function (xhr) {
            requestError(xhr, 'Failed to load SEO metadata.');
        }).always(function () {$wrapper.removeClass('loading');});
    }

    function initTargetSelect() {
        const $select = $('#seoable_id');
        if (!$select.length || !$.fn.select2) return;

        if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');

        $select.select2({
            width:'100%',
            dropdownParent:$modal,
            placeholder:'Search content item...',
            minimumInputLength:0,
            ajax:{
                url:$page.data('targets-url'),
                dataType:'json',
                delay:250,
                data:function (params) {
                    return {
                        type:$('#seoable_type_key').val(),
                        search:params.term || '',
                        selected:$select.val() || ''
                    };
                },
                processResults:function (res) {
                    return {results:res.data || []};
                }
            }
        });
    }

    function initializeForm() {
        initTargetSelect();
        document.dispatchEvent(new CustomEvent('admin:content-updated'));
    }

    function openModal(url, title) {
        destroyEditors();
        $('#modal-title').text(title);
        $modalBody.html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-primary"></i></div>');
        $modal.modal({backdrop:'static',keyboard:false,show:true});
        $.get(url).done(function (res) {
            $modalBody.html(res.html);
            initializeForm();
        }).fail(function (xhr) {
            $modalBody.html('<div class="alert alert-danger">'+(xhr.responseJSON?.message || 'Failed to load content.')+'</div>');
        });
    }

    $('#btnAddRecord').on('click',()=>openModal($page.data('create-url'),'Create SEO Metadata'));
    $(document).on('click','.btn-edit',function(){openModal($(this).data('url'),'Edit SEO Metadata')});
    $(document).on('click','.btn-show',function(){openModal($(this).data('url'),'SEO Metadata Details')});

    $(document).on('change','#seoable_type_key',function(){
        const $target = $('#seoable_id');
        if ($target.hasClass('select2-hidden-accessible')) $target.select2('destroy');
        $target.empty();
        initTargetSelect();
    });

    $(document).on('change','#social_image',function(){
        $('#social_image_media_id').val('');
        $('#social_image_remove').val('0');
        const file=this.files && this.files[0];
        if(!file) return;
        const reader=new FileReader();
        reader.onload=e=>{
            $('#social_image_preview').attr('src',e.target.result);
            $('#social_image_preview_wrap').removeClass('d-none');
        };
        reader.readAsDataURL(file);
    });

    $(document).on('click','#btnChooseSocialImage',function(){
        if(!window.MediaPicker || typeof window.MediaPicker.open!=='function'){
            toast('Media Picker is not available on this page.','error'); return;
        }
        window.MediaPicker.open(function(media){
            if(!media || !media.id || !media.url) return;
            $('#social_image').val('');
            $('#social_image_media_id').val(media.id);
            $('#social_image_remove').val('0');
            $('#social_image_preview').attr('src',media.url);
            $('#social_image_preview_wrap').removeClass('d-none');
        },{type:'image',multiple:false,title:'Choose SEO Social Image'});
    });

    $(document).on('click','#btnRemoveSocialImage',function(){
        $('#social_image').val('');
        $('#social_image_media_id').val('');
        $('#social_image_remove').val('1');
        $('#social_image_preview').attr('src','');
        $('#social_image_preview_wrap').addClass('d-none');
    });

    $(document).on('submit','#filterForm',function(e){e.preventDefault();loadData()});
    $(document).on('change','#filter_type,#filter_sitemap,#filter_robots',()=>loadData());
    $(document).on('input','#table_search',function(){clearTimeout(searchTimer);searchTimer=setTimeout(()=>loadData(),400)});
    $('#btnResetFilter').on('click',function(){document.getElementById('filterForm')?.reset();loadData()});
    $(document).on('click','#content-wrapper .pagination a',function(e){e.preventDefault();loadData($(this).attr('href'))});

    $(document).on('submit','#ajax-form',function(e){
        e.preventDefault();
        if(window.tinymce) window.tinymce.triggerSave();
        const form=this,$form=$(form),$btn=$form.find('button[type="submit"]');
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('[class*="error-"]').text('');
        $btn.prop('disabled',true);
        $.ajax({url:$form.attr('action'),type:'POST',data:new FormData(form),processData:false,contentType:false})
        .done(function(res){$modal.modal('hide');toast(res.message,'success');loadData(null,false)})
        .fail(function(xhr){
            if(xhr.status===422 && xhr.responseJSON?.errors){
                Object.entries(xhr.responseJSON.errors).forEach(([field,messages])=>{
                    const base=field.split('.')[0];
                    $form.find('[name="'+base+'"],[name="'+base+'[]"]').addClass('is-invalid');
                    $form.find('.error-'+field.replace(/\./g,'_')+',.error-'+base).first().text(messages[0]).show();
                });
                toast('Please correct the highlighted fields.','error'); return;
            }
            requestError(xhr);
        }).always(()=>$btn.prop('disabled',false));
    });

    $(document).on('change','#checkAll',function(){$('.row-checkbox').prop('checked',this.checked)});

    function doRowAction(url, method, title, text) {
        confirmAction(title,text).then(function(result){
            if(!result.isConfirmed) return;
            $.ajax({url,type:method,data:{_token:'{{ csrf_token() }}'}})
            .done(res=>{toast(res.message);loadData(null,false)})
            .fail(xhr=>requestError(xhr));
        });
    }

    $(document).on('click','.btn-delete',function(){doRowAction($(this).data('url'),'DELETE','Move SEO Metadata to Trash?','You can restore it later.')});
    $(document).on('click','.btn-restore',function(){doRowAction($(this).data('url'),'POST','Restore SEO Metadata?','Restore this metadata record?')});
    $(document).on('click','.btn-force-delete',function(){doRowAction($(this).data('url'),'DELETE','Permanently Delete SEO Metadata?','The record and its owned social image will be permanently removed.')});

    $('#btnApplyBulk').on('click',function(){
        const action=$('#bulk_action').val();
        const ids=$('.row-checkbox:checked').map(function(){return $(this).val()}).get();
        if(!action){toast('Please select a bulk action.','warning');return}
        if(!ids.length){toast('Please select at least one record.','warning');return}
        const run=()=>$.post($page.data('bulk-url'),{_token:'{{ csrf_token() }}',action,ids})
            .done(res=>{toast(res.message);$('#bulk_action').val('');loadData(null,false)})
            .fail(xhr=>requestError(xhr));
        confirmAction('Confirm Bulk Action','Apply '+action+' to '+ids.length+' record(s)?').then(r=>{if(r.isConfirmed)run()});
    });

    $modal.on('hidden.bs.modal',function(){destroyEditors();$modalBody.empty()});
});
</script>
@endpush
