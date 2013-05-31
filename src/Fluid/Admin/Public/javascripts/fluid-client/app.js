$(document).ready(function(){
    var iframe = document.all["website"].contentWindow.document;

    // Append the token and branch to local links
    $('[href]', iframe.body).click(function(e) {
        var target = $(e.target)[0];
        if (target.hostname == document.location.hostname) {
            e.preventDefault();
            iframe.location =
                updateQueryStringParameter(
                    updateQueryStringParameter(target.href, 'fluidBranch', fluidBranch),
                    'fluidBranchToken',
                    fluidNewPageToken
                );
        }
    });
});