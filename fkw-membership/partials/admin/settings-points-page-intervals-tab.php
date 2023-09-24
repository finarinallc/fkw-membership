<?php echo get_submit_button('Add Points Interval', 'primary', 'add-points-interval-btn'); ?>

<?php if ( !empty( $points ) ) { ?>
    <table class="wp-list-table widefat fixed striped">
        <thead>
        <tr>
            <th>For Level</th>
            <th>Interval</th>
            <th>Points Per</th>
            <th>Status</th>
            <th>Created</th>
            <th>Modified</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
            <?php foreach ($points as $point) { ?>
            <tr>
                <td><?php echo $point['level_name']; ?></td>
                <td>Every <?php echo $point['points_interval']; ?> <?php echo ucfirst( $point['points_interval_type'] ); ?>(s)</td>
                <td><?php echo $point['points_per']; ?></td>
                <td><?php echo $point['active'] == 1 ? 'Active' : 'Disabled'; ?></td>
                <td><?php echo $point['created']; ?></td>
				<td><?php echo $point['modified']; ?></td>
				<td>
					<a href="#" data-points-interval-id="<?php echo $point['id']; ?>" class="edit-points-interval-btn button">Edit</a>
					<a href="#" data-points-interval-id="<?php echo $point['id']; ?>" class="delete-points-interval-btn button">Delete</a>
				</td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
<?php } else { ?>
    <p>There are no points intervals configured.</p>
<?php } ?>

<div id="points-interval-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 id="modal-title">Add PointsInterval</h2>
        <form method="post" id="points-interval-form">
            <input type="hidden" name="action" id="points_interval_action" value="add_points_interval">
            <table class="form-table">
                <tr valign="top">
                    <td>
                        <div class="modify-fields-group">
                            <div class="fields-group">
                                <label for="level_id">For Level:</label>
                                <select name="level_id" id="level_id" class="form-control">
                                    <option value="" disabled selected>Please select a level</option>
                                <?php foreach ( $levels as $level ): ?>
                                    <option value="<?php echo $level['id']; ?>"><?php echo $level['level_name']; ?></option>
                                <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="fields-group">
                                <input type="hidden" name="points_id" id="points_id" value="">
                                <input type="hidden" name="modified" id="points_modified" value="<?php echo date('Y-m-d H:i:s', strtotime( 'now' ) ); ?>">
                                On every - <br>
                                <label for="points_interval">Interval:</label>
                                <div class="interval-wrapper">
                                    <input type="number" name="points_interval" id="points_interval" class="form-control" required>
                                    <select name="points_interval_type" id="points_interval_type" class="form-control" required>
                                        <option value="" disabled selected>Please select an interval type</option>
                                        <option value="day">Day(s)</option>
                                        <option value="week">Week(s)</option>
                                        <option value="month">Month(s)</option>
                                        <option value="year">Year(s)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="fields-group">
                                - add this many points: <br>
                                <label for="points_per">Points (0.5 increments allowed):</label>
                                <input type="number" name="points_per" id="points_per" step="0.5" class="form-control" required>
                            </div>
                            <div class="fields-group">
                                <label for="points_status">Status:</label>
                                <select name="points_status" id="points_status" class="form-control" required>
                                    <option value="1">Active</option>
                                    <option value="0">Disabled</option>
                                </select>
                            </div>
                            <div class="fields-group">
                                <?php wp_nonce_field( 'save_points_interval', 'save_points_interval_nonce' ); ?>
                                <input type="submit" id="modify-points-interval-submit" class="button-primary" value="Save Points Interval">
                            </div>
                        </div>
                        <div class="delete-fields-group">
                            <p>Are you sure you want to delete this points interval? This cannot be undone.</p>
                            <br>
                            <input type="hidden" name="delete_id" id="delete_id" value="">
                            <button type="submit" id="delete-points-interval-submit" class="button-primary">Delete Points Interval</button>
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>

<script>
	// JavaScript to handle the modal functionality
	var pointsIntervalModal = document.getElementById('points-interval-modal');
	var modalTitle = document.getElementById('modal-title');
	var closeModalButton = pointsIntervalModal.getElementsByClassName('close')[0];
	var pointsIntervalForm = document.getElementById('points-interval-form');

	var editPointsIntervalButtons = document.getElementsByClassName('edit-points-interval-btn');
	var deletePointsIntervalButtons = document.getElementsByClassName('delete-points-interval-btn');
	var addPointsIntervalButton = document.getElementById('add-points-interval-btn');

	var modifyPointsIntervalButton = document.getElementById('modify-points-interval-submit');
	var deletePointsIntervalButton = document.getElementById('delete-points-interval-submit');

	function openModal(title) {
		modalTitle.textContent = title;

		if (title == 'Add Points Interval' || title == 'Edit Points Interval') {
			pointsIntervalForm.querySelector('.delete-fields-group').classList.remove('show');
			pointsIntervalForm.querySelector('.modify-fields-group').classList.add('show');
		} else if (title == 'Delete Points Interval') {
			pointsIntervalForm.querySelector('.delete-fields-group').classList.add('show');
			pointsIntervalForm.querySelector('.modify-fields-group').classList.remove('show');
		}

		pointsIntervalModal.classList.add('show');
	}

	function closeModal() {
		pointsIntervalModal.classList.remove('show');
	}

	// Function to retrieve pointsInterval data by ID (replace with your logic)
	function getPointsIntervalDataById(pointsIntervalId) {

		var pointsIntervalDataArray = <?php echo json_encode($points); ?>;

		// Find the pointsInterval data based on the pointsIntervalName
		var pointsIntervalData = pointsIntervalDataArray.find(function(pointsInterval) {
			return pointsInterval.id == pointsIntervalId;
		});

		return pointsIntervalData;

	}

	// add pointsInterval button logic
	addPointsIntervalButton.addEventListener('click', function(event) {
		event.preventDefault();
		openModal('Add Points Interval');
		// Clear the form fields in the modal
		pointsIntervalForm.querySelector('#points_interval_action').value = 'add_points_interval';
		pointsIntervalForm.querySelector('#points_id').value = '';
        pointsIntervalForm.querySelector('#level_id').value = '';
		pointsIntervalForm.querySelector('#points_interval').value = '';
        pointsIntervalForm.querySelector('#points_interval_type').value = '';
		pointsIntervalForm.querySelector('#points_per').value = '';
        pointsIntervalForm.querySelector('#points_status').value = 1;
	});

	// edit pointsInterval button logic
	for (var i = 0; i < editPointsIntervalButtons.length; i++) {
		editPointsIntervalButtons[i].addEventListener('click', function(event) {
			event.preventDefault();
			openModal('Edit Points Interval');
			// Populate the modal form with data
			var pointsIntervalId = this.getAttribute('data-points-interval-id');
			var pointsIntervalName = this.getAttribute('data-points-interval-name');
			var pointsIntervalData = getPointsIntervalDataById(pointsIntervalId);

			// Populate the pointsInterval access select box based on pointsIntervalData.pointsInterval_access
			pointsIntervalForm.querySelector('#points_interval_action').value = 'edit_points_interval';
			pointsIntervalForm.querySelector('#points_id').value = String(pointsIntervalData.id);
			pointsIntervalForm.querySelector('#points_interval').value = pointsIntervalData.points_interval;
            pointsIntervalForm.querySelector('#points_interval_type').value = pointsIntervalData.points_interval_type;
            pointsIntervalForm.querySelector('#points_per').value = pointsIntervalData.points_per;
            pointsIntervalForm.querySelector('#points_status').value = pointsIntervalData.active;

			var pointsIntervalLevelSelect = pointsIntervalForm.querySelector('#level_id');

            // Get the level ID from the database
            var selectedLevelId = pointsIntervalData.level_id;

			for (var i = 0; i < pointsIntervalLevelSelect.options.length; i++) {
                if (pointsIntervalLevelSelect.options[i].value === selectedLevelId) {
                    pointsIntervalLevelSelect.options[i].selected = true;
                    break;
                }
            }
		});
	}

	modifyPointsIntervalButton.addEventListener('click', function(event) {
		var levelField = pointsIntervalForm.querySelector('[name="level_id"]'),
            intervalField = pointsIntervalForm.querySelector('[name="points_interval"]'),
            intervalTypeField = pointsIntervalForm.querySelector('[name="points_interval_type"]'),
            perField = pointsIntervalForm.querySelector('[name="points_per"]');

        if (levelField.value.trim() === '') {
			alert('Please select a level to apply this interval to.');
			event.preventDefault();
		}

		if (intervalField.value.trim() === '') {
			alert('Please enter a valid number for the points interval.');
			event.preventDefault();
		}

        if (intervalTypeField.value.trim() === '') {
			alert('Please select an interval type.');
			event.preventDefault();
		}

		if (perField.selectedOptions.length === 0) {
			alert('Please enter a valid number for the points per interval.');
			event.preventDefault();
		}
	});

	// delete pointsInterval button logic
	for (var i = 0; i < deletePointsIntervalButtons.length; i++) {
		deletePointsIntervalButtons[i].addEventListener('click', function(event) {
			event.preventDefault();
			openModal('Delete Points Interval');
			// Set up delete confirmation in the modal
			var pointsIntervalId = this.getAttribute('data-points-interval-id');
			pointsIntervalForm.querySelector('#points_interval_action').value = 'delete_points_interval';
			pointsIntervalForm.querySelector('#delete_id').value = pointsIntervalId;
		});
	}

	closeModalButton.addEventListener('click', function() {
		closeModal();
	});

	window.onclick = function (event) {
		if (event.target === pointsIntervalModal) {
			closeModal();
		}
	}
</script>
