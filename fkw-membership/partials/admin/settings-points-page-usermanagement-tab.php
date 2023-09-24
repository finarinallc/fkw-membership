<?php if ( !empty( $points_users ) ) { ?>
    <table class="wp-list-table widefat fixed striped">
        <thead>
        <tr>
            <th>User</th>
			<th>Level</th>
            <th>Quantity</th>
			<th>Actions</th>
        </tr>
        </thead>
        <tbody>
            <?php foreach ($points_users as $point_user) { ?>
            <tr>
                <td><?php echo $point_user['user_name']; ?></td>
				<td><?php echo !empty( $point_user['level_name'] ) ? $point_user['level_name'] : 'No Level Set'; ?></td>
                <td><?php echo $point_user['total_points']; ?></td>
                <td>
					<a href="#" data-points-users-id="<?php echo $point_user['user_id']; ?>" data-points-users-name="<?php echo $point_user['user_name']; ?>" class="modify-points-users-btn button">Modify Points Quantity</a>
				</td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
<?php } ?>

<div id="points-users-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 id="modal-title">Modify Points Quantity for User <span id="points-users-name"></span></h2>
        <form method="post" id="points-users-form">
            <input type="hidden" name="action" id="points_users_action" value="modify_points_users">
            <table class="form-table">
                <tr valign="top">
                    <td>
                        <div class="modify-fields-group show">
                            <div class="fields-group">
                                <label for="points_per">Quantity:</label>
                                <input type="number" name="new_points_quantity" id="new_points_quantity" step="0.5" class="form-control" required>
                                <input type="hidden" name="points_user_id" id="points_user_id" value="<?php echo $point_user['user_id']; ?>">
                            </div>
                            <div class="fields-group">
                                <?php wp_nonce_field( 'save_points_users', 'save_points_users_nonce' ); ?>
                                <input type="submit" id="modify-points-users-submit" class="button-primary" value="Save New Points Quantity">
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>

<script>
	// JavaScript to handle the modal functionality
	var pointsUserQuantityModal = document.getElementById('points-users-modal');
	var closeModalButton = pointsUserQuantityModal.getElementsByClassName('close')[0];
	var pointsUserQuantityForm = document.getElementById('points-users-form');

	var modifyPointsQuantityButtons = document.getElementsByClassName('modify-points-users-btn');
	var modifyPointsIntervalButton = document.getElementById('modify-points-users-submit');

	function openModal(title) {
		pointsUserQuantityModal.classList.add('show');
	}

	function closeModal() {
		pointsUserQuantityModal.classList.remove('show');
	}

	// Function to retrieve pointsInterval data by ID (replace with your logic)
	function getPointsQuantityDataById(userId) {

		var pointsQuantityDataArray = <?php echo json_encode($points_users); ?>;

		var pointsQuantityData = pointsQuantityDataArray.find(function(userId) {
			return userId.total_points;
		});

		return pointsQuantityData;

	}

	for (var i = 0; i < modifyPointsQuantityButtons.length; i++) {
		modifyPointsQuantityButtons[i].addEventListener('click', function(event) {
			event.preventDefault();
			openModal();

			// Populate the modal form with data
			var pointsUserId = this.getAttribute('data-points-users-id');
			var pointsQuantityData = getPointsQuantityDataById(pointsUserId);

			// Populate the pointsInterval access select box based on pointsIntervalData.pointsInterval_access
			pointsUserQuantityForm.querySelector('[name="new_points_quantity"]').value = pointsQuantityData.total_points;
            pointsUserQuantityForm.querySelector('[name="points_user_id"]').value = pointsUserId;
		});
	}

	modifyPointsIntervalButton.addEventListener('click', function(event) {
		var pointsQuantityField = pointsUserQuantityForm.querySelector('[name="new_points_quantity"]');

        if (pointsQuantityField.value.trim() === '') {
			alert('Please add a valid points quantity.');
			event.preventDefault();
		}
	});

	closeModalButton.addEventListener('click', function() {
		closeModal();
	});

	window.onclick = function (event) {
		if (event.target === pointsUserQuantityModal) {
			closeModal();
		}
	}
</script>
