@can('media_list')
<div class="modal fade media-picker-modal" id="globalMediaPickerModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="globalMediaPickerTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header align-items-center">
                <div>
                    <h5 class="modal-title font-weight-bold mb-1" id="globalMediaPickerTitle">
                        <i class="fas fa-photo-video text-primary mr-2"></i>Media Library
                    </h5>
                    <small class="text-muted">Upload, search, select and reuse existing project media.</small>
                </div>
                <button type="button" class="close shadow-none" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-0">
                @can('media_upload')
                    <div class="media-picker-upload-zone m-3" id="mediaPickerDropzone" role="button" tabindex="0" aria-label="Upload media files">
                        <input type="file" id="mediaPickerFiles" class="d-none" multiple accept="image/jpeg,image/png,image/webp,image/gif,image/x-icon,video/mp4,video/webm,application/pdf">
                        <div class="text-center py-3">
                            <i class="fas fa-cloud-upload-alt fa-2x text-primary mb-2"></i>
                            <div class="font-weight-bold">Drop files here or click to upload</div>
                            <small class="text-muted">Maximum 10 files, 10MB each. JPG, PNG, WEBP, GIF, ICO, MP4, WEBM, PDF.</small>
                        </div>
                    </div>
                    <div class="px-3 d-none" id="mediaPickerUploadProgressWrap">
                        <div class="progress" style="height: 7px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" id="mediaPickerUploadProgress" style="width: 0%"></div>
                        </div>
                    </div>
                @endcan

                <div class="media-picker-toolbar border-top border-bottom p-3 bg-light">
                    <div class="row align-items-end">
                        <div class="col-lg-4 col-md-6 mb-2 mb-lg-0">
                            <label class="small text-muted font-weight-bold mb-1">Search</label>
                            <div class="input-group input-group-sm">
                                <input type="search" class="form-control" id="mediaPickerSearch" placeholder="Search file, collection, owner..." autocomplete="off">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="mediaPickerClearSearch" title="Clear search"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-3 mb-2 mb-lg-0">
                            <label class="small text-muted font-weight-bold mb-1">Type</label>
                            <select class="form-control form-control-sm" id="mediaPickerType">
                                <option value="">All Types</option>
                                <option value="image">Images</option>
                                <option value="video">Videos</option>
                                <option value="file">Files</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-3 mb-2 mb-lg-0">
                            <label class="small text-muted font-weight-bold mb-1">Collection</label>
                            <select class="form-control form-control-sm" id="mediaPickerCollection">
                                <option value="">All Collections</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6 mb-2 mb-lg-0">
                            <label class="small text-muted font-weight-bold mb-1">Owner</label>
                            <select class="form-control form-control-sm" id="mediaPickerOwner">
                                <option value="">All Owners</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <div class="btn-group btn-group-sm btn-block" role="group">
                                <button type="button" class="btn btn-primary" id="mediaPickerRefresh" title="Refresh"><i class="fas fa-sync-alt"></i></button>
                                @can('media_trash')
                                    <button type="button" class="btn btn-outline-danger" id="mediaPickerTrashToggle" data-trash="0" title="Open trash"><i class="fas fa-trash-alt"></i></button>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap px-3 py-2 border-bottom" style="gap:8px;">
                    <div class="d-flex align-items-center flex-wrap" style="gap:8px;">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="mediaPickerSelectPage">
                            <label class="custom-control-label small font-weight-bold" for="mediaPickerSelectPage">Select page</label>
                        </div>
                        <span class="badge badge-primary" id="mediaPickerSelectedCount">0 selected</span>
                        <button type="button" class="btn btn-link btn-sm p-0" id="mediaPickerClearSelection">Clear</button>
                    </div>

                    <div class="d-flex align-items-center" style="gap:6px;">
                        @can('media_delete')
                            <button type="button" class="btn btn-outline-danger btn-sm media-picker-bulk-active" id="mediaPickerBulkTrash" disabled>
                                <i class="fas fa-trash-alt mr-1"></i>Trash Selected
                            </button>
                        @endcan
                        @can('media_restore')
                            <button type="button" class="btn btn-outline-success btn-sm media-picker-bulk-trash d-none" id="mediaPickerBulkRestore" disabled>
                                <i class="fas fa-undo mr-1"></i>Restore
                            </button>
                        @endcan
                        @can('media_force_delete')
                            <button type="button" class="btn btn-danger btn-sm media-picker-bulk-trash d-none" id="mediaPickerBulkForceDelete" disabled>
                                <i class="fas fa-fire mr-1"></i>Force Delete
                            </button>
                        @endcan
                    </div>
                </div>

                <div class="p-3 media-picker-grid-wrap">
                    <div class="row" id="mediaPickerGrid"></div>
                    <div class="text-center py-5 d-none" id="mediaPickerEmpty">
                        <i class="far fa-images fa-3x text-muted mb-3"></i>
                        <h6 class="font-weight-bold text-muted">No media found</h6>
                        <p class="text-muted small mb-0">Try changing the search or filter options.</p>
                    </div>
                    <div class="text-center py-5" id="mediaPickerLoading">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary mb-2"></i>
                        <div class="text-muted font-weight-bold">Loading media...</div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top bg-light">
                    <small class="text-muted" id="mediaPickerMeta">Showing 0 media</small>
                    <nav aria-label="Media picker pagination">
                        <ul class="pagination pagination-sm mb-0" id="mediaPickerPagination"></ul>
                    </nav>
                </div>
            </div>

            <div class="modal-footer justify-content-between">
                <small class="text-muted"><i class="fas fa-lock mr-1"></i>Private lead attachments are excluded from reusable picker results.</small>
                <div>
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="mediaPickerUseSelected" disabled>
                        <i class="fas fa-check mr-1"></i>Use Selected
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endcan
