        </div> <!-- wrapper div -->
        </div> <!--  container div -->

        <footer class="bg-light text-center text-lg-start mt-4">
            <div class="text-center p-3">
            © 2025 Loving CRM. Licensed under 
            <a href="/license.html" target="_blank" rel="noopener noreferrer">AGPL-3.0+</a>.
                </a>.
            </div>
        </footer>

        <!-- modal popup for add notes on list contacts-->
        <div class="modal fade" id="editNotesModal" tabindex="-1" aria-labelledby="editNotesModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editNotesModalLabel">Edit Notes</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <textarea id="editNotesTextarea" class="form-control" rows="5"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveNotesBtn">Save changes</button>
                    </div>
                </div>
            </div>
        </div>