(function($) {
    // Get the server details from the localized script
    var serverId = serverDetails.server_id;
    var apiKey = serverDetails.api_key;

    // Use the Discord API to fetch server data
    $.ajax({
        url: 'https://discord.com/api/v10/guilds/' + serverId,
        type: 'GET',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('Authorization', 'Bot ' + apiKey);
        },
        success: function(data) {
            // Handle the data returned from the Discord server
            console.log(data); // You can do something with the data here
        },
        error: function(xhr, status, error) {
            // Handle any errors
            console.log(error);
        }
    });
})(jQuery);
