@push('js')
<script>
$(function(){
    $.ajaxSetup({headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}});
    const $page=$('#tracking-rule-manager'),$wrapper=$('#content-wrapper'),$modal=$('#ajaxModal'),$body=$('#modal-body');
    let timer=null;
    const toast=(m,t='success')=>window.showAlert?window.showAlert(m,t):Promise.resolve();
    const confirmBox=(title,text)=>window.showConfirm?window.showConfirm({title,text}):Promise.resolve({isConfirmed:window.confirm(text)});
    const fail=(xhr,msg='Request failed.')=>toast(xhr.responseJSON?.message||msg,'error');

    function filterUrl(){const $f=$('#filterForm'),q=$f.serialize();return q?$f.attr('action')+'?'+q:$f.attr('action')}
    function loadData(u=null,push=true){const ru=u||filterUrl();$wrapper.addClass('loading');$.get(ru).done(r=>{$wrapper.html(r.html);$('#checkAll').prop('checked',false);if(push&&history.replaceState)history.replaceState({},'',ru)}).fail(x=>fail(x,'Failed to load event rules.')).always(()=>$wrapper.removeClass('loading'))}
    function openModal(u,title){$('#modal-title').text(title);$body.html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-primary"></i></div>');$modal.modal({backdrop:'static',keyboard:false,show:true});$.get(u).done(r=>{$body.html(r.html);document.dispatchEvent(new CustomEvent('admin:content-updated'))}).fail(x=>$body.html('<div class="alert alert-danger">'+(x.responseJSON?.message||'Failed to load.')+'</div>'))}

    $('#btnAddRecord').on('click',()=>openModal($page.data('create-url'),'Create Tracking Event Rule'));
    $(document).on('click','.btn-edit',function(){openModal($(this).data('url'),'Edit Tracking Event Rule')});
    $(document).on('click','.btn-show',function(){openModal($(this).data('url'),'Tracking Event Rule Details')});
    $(document).on('submit','#filterForm',e=>{e.preventDefault();loadData()});
    $(document).on('change','#filter_provider_id,#filter_internal_event,#filter_status',()=>loadData());
    $(document).on('input','#table_search',function(){clearTimeout(timer);timer=setTimeout(()=>loadData(),400)});
    $('#btnResetFilter').on('click',()=>{document.getElementById('filterForm')?.reset();loadData()});
    $(document).on('click','#content-wrapper .pagination a',function(e){e.preventDefault();loadData($(this).attr('href'))});

    $(document).on('submit','#ajax-form',function(e){e.preventDefault();const $f=$(this),$b=$f.find('button[type=submit]');$b.prop('disabled',true);$f.find('.is-invalid').removeClass('is-invalid');$f.find('[class*="error-"]').text('');$.ajax({url:$f.attr('action'),type:'POST',data:new FormData(this),processData:false,contentType:false}).done(r=>{$modal.modal('hide');toast(r.message);loadData(null,false)}).fail(x=>{if(x.status===422&&x.responseJSON?.errors){Object.entries(x.responseJSON.errors).forEach(([k,v])=>{$f.find('[name="'+k+'"]').addClass('is-invalid');$f.find('.error-'+k.replace(/\./g,'_')).text(v[0])});toast('Please correct the highlighted fields.','error')}else fail(x)}).always(()=>$b.prop('disabled',false))});

    $(document).on('change','#checkAll',function(){$('.row-checkbox').prop('checked',this.checked)});
    $(document).on('click','.btn-toggle',function(){$.post($(this).data('url'),{_token:'{{ csrf_token() }}'}).done(r=>{toast(r.message);loadData(null,false)}).fail(fail)});
    function rowAction(sel,method,title,text){$(document).on('click',sel,function(){const u=$(this).data('url');confirmBox(title,text).then(r=>{if(!r.isConfirmed)return;$.ajax({url:u,type:method,data:{_token:'{{ csrf_token() }}'}}).done(x=>{toast(x.message);loadData(null,false)}).fail(fail)})})}
    rowAction('.btn-delete','DELETE','Move Event Rule to Trash?','You can restore it later.');
    rowAction('.btn-restore','POST','Restore Event Rule?','The rule will be restored as disabled.');
    rowAction('.btn-force-delete','DELETE','Permanently Delete Event Rule?','This cannot be undone.');

    $('#btnApplyBulk').on('click',function(){const action=$('#bulk_action').val(),ids=$('.row-checkbox:checked').map(function(){return this.value}).get();if(!action)return toast('Please select a bulk action.','warning');if(!ids.length)return toast('Please select at least one rule.','warning');confirmBox('Confirm Bulk Action','Apply '+action+' to '+ids.length+' rule(s)?').then(r=>{if(!r.isConfirmed)return;$.post($page.data('bulk-url'),{_token:'{{ csrf_token() }}',action,ids}).done(x=>{toast(x.message);$('#bulk_action').val('');loadData(null,false)}).fail(fail)})});
});
</script>
@endpush
