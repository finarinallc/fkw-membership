<?php echo get_submit_button('Add Level', 'primary', 'add-level-btn'); ?>

<div id="level-modal" class="modal">
	<div class="modal-content">
		<span class="close">&times;</span>
		<h2 id="modal-title">Add Level</h2>
		<form method="post" id="level-form">
			<input type="hidden" name="action" id="level_action" value="add_levels">
			<table class="form-table">
				<tr valign="top">
					<td>
						<div class="modify-fields-group">
							<div class="fields-group">
								<label for="name">Level Name:</label>
								<input type="hidden" name="level_id" id="level_id" value="">
								<input type="hidden" name="existing_name" id="existing_name">
								<input type="hidden" name="modified"  id="level_modified" value="<?php echo date('Y-m-d H:i:s', strtotime( 'now' ) ); ?>">
								<input type="text" name="level_name" id="level_name" class="form-control">
							</div>
							<div class="fields-group">
								<label for="level_access">Level Access:</label>
								<select name="level_access[]" id="level_access" class="form-control" multiple>
									<option value="no_access">No Access</option>
									<?php foreach( $access_options as $key => $value ) {
										if( !empty( $value ) && $value == 1 ) { ?>
									<option value="<?php echo $key; ?>">
										<?php echo ucwords( str_replace( '_', ' ', $key ) ); ?>
									</option>
									<?php }
									} ?>
								</select>
								<label for="level_free">Will this level be free to subscribe to?:</label>
								<select name="level_free[]" id="level_free" class="form-control">
									<option value="0">No</option>
									<option value="1">Yes</option>
								</select>
							</div>
							<div class="fields-group">
								<?php wp_nonce_field( 'save_member_level', 'save_member_level_nonce' ); ?>
								<input type="submit" id="modify-level-submit" class="button-primary" value="Save Level">
							</div>
						</div>
						<div class="delete-fields-group">
							<p>Are you sure you want to delete level <span class="delete-level-name"></span>? This cannot be undone.</p>
							<br>
							<input type="hidden" name="delete_id" id="delete_id" value="">
							<button type="submit" id="delete-level-submit" class="button-primary">Delete Level</button>
						</div>
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>

<?php if ( !empty( $levels ) ) { ?>
<table class="wp-list-table widefat fixed striped">
	<thead>
		<tr>
			<th>Level Name</th>
			<th>Level Access</th>
			<th>Free?</th>
			<th>Created</th>
			<th>Modified</th>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($levels as $level) { ?>
			<tr>
				<td><?php echo $level['level_name']; ?></td>
				<td>
					<?php
					// Display the access options for the level
					$levels_access_options = array();
					$level['level_access'] = unserialize( $level['level_access'] );

					foreach ( $access_options as $key => $value ) {

						if ( in_array( $key, $level['level_access'] ) ) {
							$levels_access_options[] = ucwords( str_replace( '_', ' ', $key ) );
						}

					}

					echo implode(', ', $levels_access_options);
					?>
				</td>
				<td><?php echo $level['free'] === 1 ? '<span style="color: green; font-size: 20px; font-wieght: bold;">&#9679;</span> ': '<span style="color: red; font-size: 20px;">&#10006;</span>'; ?></td>
				<td><?php echo $level['created']; ?></td>
				<td><?php echo $level['modified']; ?></td>
				<td>
					<a href="#" data-level-id="<?php echo $level['id']; ?>" data-level-name="<?php echo $level['level_name']; ?>" class="edit-level-btn button">Edit</a>
					<a href="#" data-level-id="<?php echo $level['id']; ?>" data-level-name="<?php echo $level['level_name']; ?>" class="delete-level-btn button">Delete</a>
				</td>
			</tr>
		<?php } ?>
	</tbody>
</table>
<?php } else { ?>
<p>No levels created yet.</p>
<?php } ?>
</div>

<script>
	// JavaScript to handle the modal functionality
	var levelModal = document.getElementById('level-modal');
	var modalTitle = document.getElementById('modal-title');
	var closeModalButton = levelModal.getElementsByClassName('close')[0];
	var levelForm = document.getElementById('level-form');

	var editLevelButtons = document.getElementsByClassName('edit-level-btn');
	var deleteLevelButtons = document.getElementsByClassName('delete-level-btn');
	var addLevelButton = document.getElementById('add-level-btn');

	var modifyLevelButton = document.getElementById('modify-level-submit');
	var deleteLevelButton = document.getElementById('delete-level-submit');

	function openModal(title) {
		modalTitle.textContent = title;

		if( title == 'Add Level' || title == 'Edit Level' ) {
			levelForm.querySelector('.delete-fields-group').style.display = 'none';
			levelForm.querySelector('.modify-fields-group').style.display = 'block';
		} else if ( title == 'Delete Level' ) {
			levelForm.querySelector('.delete-fields-group').style.display = 'block';
			levelForm.querySelector('.modify-fields-group').style.display = 'none';
		}

		levelModal.style.display = 'block';
	}

	function closeModal() {
		levelModal.style.display = 'none';
	}

	// Function to retrieve level data by ID (replace with your logic)
	function getLevelDataById(levelId) {

		var levelDataArray = <?php echo json_encode($levels); ?>;

		// Find the level data based on the levelName
		var levelData = levelDataArray.find(function(level) {
			return level.id == levelId;
		});

		return levelData;

	}

	// add level button logic
	addLevelButton.addEventListener('click', function(event) {
		event.preventDefault();
		openModal('Add Level');
		// Clear the form fields in the modal
		levelForm.querySelector('#level_action').value = 'add_levels';
		levelForm.querySelector('#existing_name').value = '';
		levelForm.querySelector('#level_name').value = '';
		levelForm.querySelector('#level_access').value = [];
		levelForm.querySelector('#level_access').value = [0];
	});

	// edit level button logic
	for (var i = 0; i < editLevelButtons.length; i++) {
		editLevelButtons[i].addEventListener('click', function(event) {
			event.preventDefault();
			openModal('Edit Level');
			// Populate the modal form with data
			var levelId = this.getAttribute('data-level-id');
			var levelName = this.getAttribute('data-level-name');
			var levelData = getLevelDataById(levelId);

			// Populate the level access select box based on levelData.level_access
			levelForm.querySelector('#level_action').value = 'edit_levels';
			levelForm.querySelector('#level_id').value = String(levelData.id);
			levelForm.querySelector('#level_name').value = levelData.level_name;
			levelForm.querySelector('#existing_name').value = levelData.level_name;
			levelForm.querySelector('#level_free').value = levelData.free;

			var levelAccessSelect = levelForm.querySelector('#level_access');

			// Clear any existing options
			levelAccessSelect.innerHTML = '';

			// Populate the level access select box based on levelData.level_access
			var accessSettings = <?php echo json_encode( $access_options ); ?>;

			for (var key in accessSettings) {
				if (accessSettings.hasOwnProperty(key)) {
					var settingValue = accessSettings[key];

					if (settingValue == 1) {
						var option = document.createElement('option');
						option.value = key;
						option.textContent = key.replace(/_/g, ' ').replace(/\b\w/g, function(c) {
							return c.toUpperCase();
						});

						if (levelData.level_access.indexOf(key) !== -1) {
							option.selected = true;
						}


						levelAccessSelect.appendChild(option);
					}
				}
			}
		});
	}

	modifyLevelButton.addEventListener('click', function(event) {
		var nameField = levelForm.querySelector('[name="name"]');
		var accessField = levelForm.querySelector('[name="access[]"]');

		if (nameField.value.trim() === '') {
			alert('Please enter a valid level name.');
			event.preventDefault();
		}

		if (accessField.selectedOptions.length === 0) {
			alert('Please select at least one access option.');
			event.preventDefault();
		}
	});

	// delete level button logic
	for (var i = 0; i < deleteLevelButtons.length; i++) {
		deleteLevelButtons[i].addEventListener('click', function(event) {
			event.preventDefault();
			openModal('Delete Level');
			// Set up delete confirmation in the modal
			var levelId = this.getAttribute('data-level-id'),
				levelName = this.getAttribute('data-level-name');
			levelForm.querySelector('#level_action').value = 'delete_levels';
			levelForm.querySelector('#delete_id').value = levelId;
			levelForm.querySelector('.delete-level-name').innerText = levelName;
		});
	}

	closeModalButton.addEventListener('click', function() {
		closeModal();
	});

	window.onclick = function (event) {
		if (event.target === levelModal) {
			closeModal();
		}
	}
</script>
