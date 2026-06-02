<!-- Large Modal -->
<div class="modal fade" id="largeModal" tabindex="-1" aria-labelledby="largeModal-label" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="largeModal-label"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="spinner-border text-light m-2" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Small Modal -->
<div class="modal fade" id="smallModal" tabindex="-1" aria-labelledby="smallModal-label" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="smallModal-label"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="spinner-border text-light m-2" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModal-label" aria-modal="true" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center">
                <form method="POST" class="ajaxDeleteForm" action="" id="delete_form">
                    @csrf
                    @method('DELETE')
                    <i class="fa-solid fa-circle-info" style="font-size: 50px; color: #6c757d;"></i>
                    <p class="mt-3">Are you sure?</p>
                    <div class="d-flex justify-content-center gap-2 mt-2">
                        <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa-solid fa-xmark"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i> Continue
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Clone Confirmation Modal -->
<div class="modal fade" id="cloneModal" tabindex="-1" aria-labelledby="cloneModal-label" aria-modal="true" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center">
                <i class="fa-solid fa-copy" style="font-size: 50px; color: #6c757d;"></i>
                <p class="mt-3">Are you sure you want to clone this page?</p>
                <div class="d-flex justify-content-center gap-2 mt-2">
                    <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i> Cancel
                    </button>
                    <a href="" id="clone_confirm_btn" class="btn btn-sm btn-success">
                        <i class="fa-solid fa-copy"></i> Clone
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Delete Confirmation Modal -->
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-labelledby="bulkDeleteModal-label" aria-modal="true" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center">
                <form method="POST" class="ajaxBulkForm" action="" id="bulk_delete_form">
                    @csrf
                    @method('POST')
                    <input type="hidden" name="ids" id="bulk_delete_ids" value="">
                    <i class="fa-solid fa-trash" style="font-size: 50px; color: #dc3545;"></i>
                    <p class="mt-3" id="bulk_delete_message">Are you sure you want to delete selected items?</p>
                    <div class="d-flex justify-content-center gap-2 mt-2">
                        <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa-solid fa-xmark"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="fa-solid fa-check"></i> Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Active Confirmation Modal -->
<div class="modal fade" id="bulkActiveModal" tabindex="-1" aria-labelledby="bulkActiveModal-label" aria-modal="true" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center">
                <form method="POST" class="ajaxBulkForm" action="" id="bulk_active_form">
                    @csrf
                    @method('POST')
                    <input type="hidden" name="ids" id="bulk_active_ids" value="">
                    <i class="fa-solid fa-check-circle" style="font-size: 50px; color: #28a745;"></i>
                    <p class="mt-3" id="bulk_active_message">Are you sure you want to activate selected items?</p>
                    <div class="d-flex justify-content-center gap-2 mt-2">
                        <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa-solid fa-xmark"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="fa-solid fa-check"></i> Activate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Inactive Confirmation Modal -->
<div class="modal fade" id="bulkInactiveModal" tabindex="-1" aria-labelledby="bulkInactiveModal-label" aria-modal="true" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center">
                <form method="POST" class="ajaxBulkForm" action="" id="bulk_inactive_form">
                    @csrf
                    @method('POST')
                    <input type="hidden" name="ids" id="bulk_inactive_ids" value="">
                    <i class="fa-solid fa-times-circle" style="font-size: 50px; color: #dc3545;"></i>
                    <p class="mt-3" id="bulk_inactive_message">Are you sure you want to deactivate selected items?</p>
                    <div class="d-flex justify-content-center gap-2 mt-2">
                        <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa-solid fa-xmark"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="fa-solid fa-check"></i> Deactivate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Login As Student Confirmation Modal -->
<div class="modal fade" id="loginAsStudentModal" tabindex="-1" aria-labelledby="loginAsStudentModal-label" aria-modal="true" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center">
                <form method="POST" class="ajaxLoginAsStudentForm" action="" id="login_as_student_form">
                    @csrf
                    <i class="fa-solid fa-user-secret" style="font-size: 50px; color: #6c757d;"></i>
                    <p class="mt-3">You are about to log in as this student. Your current superadmin session will end. Do you want to continue?</p>
                    <div class="d-flex justify-content-center gap-2 mt-2">
                        <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa-solid fa-xmark"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i> Continue
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .modal{
        backdrop-filter: blur(2px);
    }
        
</style>