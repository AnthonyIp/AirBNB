$('#add-image').click(() => {
    const image = $('#ad_images');
    const index = +$('#widgets-counter').val();
    /*Je recupere le prototype des entrees*/
    const tmpl = image.data('prototype').replace(/_name_/g, index);

    /*injection du template dans la div*/
    image.append(tmpl);

    console.log(index);
    $('#widgets-counter').val(index + 1);
    console.log($('#widgets-counter').val());

    /*Gestion du boutton supprimer*/
    handleDeleteButtons();
});

function handleDeleteButtons() {
    $('button[data-action="delete"]').click(function () {
        const target = $(this).data('target');
        $(target).remove();
    })
}

function updateCounter() {
    const count = +$('#ad_images div.form-group').length;
    $('#widgets-counter').val(count)
}

updateCounter();
handleDeleteButtons();
