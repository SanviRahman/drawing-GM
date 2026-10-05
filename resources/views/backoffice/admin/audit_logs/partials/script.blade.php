<script>
$(function(){
 $.ajaxSetup({headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}});
 const $page=$('#audit-manager'),$wrapper=$('#content-wrapper'),$modal=$('#ajaxModal'),$body=$('#modal-body'); let timer=null;
 const toast=(m,t='success')=>window.showAlert?window.showAlert(m,t):Promise.resolve();
 const confirmBox=(title,text)=>window.showConfirm?window.showConfirm({title,text}):Promise.resolve({isConfirmed:window.confirm(text)});
 const fail=(xhr,m='Request failed.')=>toast(xhr.responseJSON?.message||m,'error');
 function url(){const $f=$('#filterForm'),q=$f.serialize();return q?$f.attr('action')+'?'+q:$f.attr('action')}
 function loadData(u=null){$wrapper.addClass('loading');$.get(u||url()).done(r=>$wrapper.html(r.html)).fail(x=>fail(x)).always(()=>$wrapper.removeClass('loading'))}
 $(document).on('click','.btn-show',function(){$modal.modal('show');$body.html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');$.get($(this).data('url')).done(r=>$body.html(r.html)).fail(x=>fail(x))});
 $(document).on('change','#filter_action,#filter_date_from,#filter_date_to',()=>loadData());
 $(document).on('input','#table_search',function(){clearTimeout(timer);timer=setTimeout(()=>loadData(),350)});
 $('#btnResetFilter').on('click',function(){document.getElementById('filterForm')?.reset();loadData()});
 $(document).on('click','#content-wrapper .pagination a',function(e){e.preventDefault();loadData($(this).attr('href'))});
 $(document).on('change','#checkAll',function(){$('.row-checkbox').prop('checked',this.checked)});
 function row(sel,method,title,text){$(document).on('click',sel,function(){const u=$(this).data('url');confirmBox(title,text).then(r=>{if(!r.isConfirmed)return;$.ajax({url:u,type:method}).done(x=>{toast(x.message);loadData()}).fail(fail)})})}
 row('.btn-delete','DELETE','Move audit log to Trash?','The log can be restored later.'); row('.btn-restore','POST','Restore audit log?','Restore this audit record?'); row('.btn-force-delete','DELETE','Permanently delete audit log?','This cannot be undone.');
 $('#btnApplyBulk').on('click',function(){const action=$('#bulk_action').val(),ids=$('.row-checkbox:checked').map(function(){return this.value}).get();if(!action)return toast('Select a bulk action.','warning');if(!ids.length)return toast('Select at least one record.','warning');confirmBox('Confirm bulk action','Apply '+action+' to '+ids.length+' audit log(s)?').then(r=>{if(!r.isConfirmed)return;$.post($page.data('bulk-url'),{action,ids}).done(x=>{toast(x.message);loadData()}).fail(fail)})});
 $('#btnExport').on('click',()=>window.location=$page.data('export-url')+'?'+$('#filterForm').serialize());
});
</script>
