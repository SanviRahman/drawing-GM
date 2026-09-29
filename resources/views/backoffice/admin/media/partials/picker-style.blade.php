<style>
    #globalMediaPickerModal { z-index: 1065; }
    #globalMediaPickerModal + .modal-backdrop, .media-picker-backdrop { z-index: 1060; }
    .media-picker-upload-zone {
        border: 2px dashed #88a8ff;
        border-radius: 12px;
        background: #f7f9ff;
        cursor: pointer;
        transition: .18s ease;
    }
    .media-picker-upload-zone:hover,
    .media-picker-upload-zone.is-dragging {
        background: #edf3ff;
        border-color: #007bff;
        transform: translateY(-1px);
    }
    .media-picker-grid-wrap { min-height: 320px; max-height: 52vh; overflow-y: auto; }
    .media-picker-card {
        border: 1px solid #e5e8ef;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
        cursor: pointer;
        transition: .16s ease;
        height: 100%;
        position: relative;
    }
    .media-picker-card:hover { border-color: #8ab4ff; box-shadow: 0 5px 18px rgba(32, 78, 140, .10); transform: translateY(-1px); }
    .media-picker-card.is-selected { border: 2px solid #007bff; box-shadow: 0 0 0 3px rgba(0,123,255,.12); }
    .media-picker-card-preview {
        height: 132px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f5f7fa;
        overflow: hidden;
        position: relative;
    }
    .media-picker-card-preview img { width: 100%; height: 100%; object-fit: contain; }
    .media-picker-file-icon { font-size: 2.5rem; color: #7b8794; }
    .media-picker-select-mark {
        position: absolute; top: 8px; left: 8px; width: 25px; height: 25px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center; background: rgba(255,255,255,.94);
        border: 1px solid #ccd3dc; color: transparent; z-index: 2;
    }
    .media-picker-card.is-selected .media-picker-select-mark { background: #007bff; border-color: #007bff; color: #fff; }
    .media-picker-card-actions { position: absolute; top: 7px; right: 7px; z-index: 3; }
    .media-picker-card-body { padding: 10px; }
    .media-picker-card-name { font-weight: 700; font-size: .86rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .media-picker-card-meta { color: #7a8591; font-size: .73rem; line-height: 1.45; }
    .media-picker-modal .modal-content { border-radius: 12px; overflow: hidden; }
    .media-picker-modal .modal-header { background: #fff; }
    body.media-picker-nested-open { overflow: hidden; }
    @media (max-width: 767.98px) {
        .media-picker-modal .modal-dialog { margin: .4rem; max-width: none; }
        .media-picker-grid-wrap { max-height: 48vh; }
        .media-picker-card-preview { height: 110px; }
    }
</style>
