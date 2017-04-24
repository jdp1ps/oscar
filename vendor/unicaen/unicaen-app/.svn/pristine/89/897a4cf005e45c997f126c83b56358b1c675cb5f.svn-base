/**
 * 
 */
$(function() {
    installUpload();
});

/**
 * Installe le nécessaire pour gérer l'upload de fichier en mode AJAX.
 * 
 * @returns void
 */
function installUpload() 
{
    var uploadEventFileDeleted  = "upload-event-file-deleted";
    var uploadEventFileUploaded = "upload-event-file-uploaded";
    
    $(".upload-container").each(function() {
        var container = $(this);
        var form      = $(".upload-form", container);
        var filesDiv  = $(".uploaded-files-div", container);
        var choose    = $(".choose-file", container);
        
        form.ajaxForm({
            success: function(responseText, statusText, xhr, form) {
                // détecte si des erreurs sont retournées
                if (responseText.errors) {
                    updateUploadContainer(container);
                    alert("Impossible de déposer le fichier!\n- " + responseText.errors.join('\n- '));
                }
                else {
                    updateUploadContainer(container, true);
                    $("body").trigger(uploadEventFileUploaded, [ container ]);
                }
            },
            error: function() {
                alert("Oups, une erreur s'est produite pendant l'envoi de fichier! Essayez à nouveau, svp.");
            },
            beforeSubmit: function(arr, $form, options) {
                // The array of form data takes the following form:
                // [ { name: 'username', value: 'jresig' }, { name: 'password', value: 'secret' } ]
                // interdiction du bouton d'envoi
                $(".upload-file", form).button('loading');
                // ajout d'un témoin de chargement AJAX
                if (! $("ul", filesDiv).length) {
                   filesDiv.html("<ul/>"); 
                }
                $("ul", filesDiv).append($("<li/>").addClass("loading"));
                // return false to cancel submit
            },
        });
        
        // chargement initial de la liste des fichiers
        filesDiv.addClass("loading").refresh([], function() { filesDiv.hide().removeClass("loading").fadeIn(); });
        
        // écoute clic sur suppression de fichier pour faire la requête AJAX et rafraîchir la liste des fichiers
        filesDiv.on("click", ".delete-file", function(event) {
            var a = $(this);
            a.button('loading');
            $.post(a.prop('href'), [], function(data, textStatus, jqXHR) {
                a.parent("li").fadeOut();
                filesDiv.refresh();
                $("body").trigger(uploadEventFileDeleted, [ container ]);
            });
            event.preventDefault();
        });
        
        // affichage/masquage bouton d'envoi selon sélection de fichier
        choose.change(function() {
            updateUploadButton(container);
        });
        
        // masquage initial du bouton d'envoi
        updateUploadButton(container); 
    });
}

/**
 * Rafraîchit la liste des fichiers déposés puis quand c'est fait :
 * - réinitialise le formulaire, si demandé ;
 * - autorise le bouton "Envoyer"
 * - affiche ou masque le bouton "Envoyer" en fonction de la situation.
 * 
 * @param object container
 * @param boolean clearForm
 */
function updateUploadContainer(container, clearForm)
{
    var form     = $(".upload-form", container);
    var filesDiv = $(".uploaded-files-div", container);
    
    filesDiv.refresh([], function() {
        if (clearForm) {
            form.clearForm(); 
        }
        $(".upload-file", form).button('reset'); 
        updateUploadButton(); 
    });
}

/**
 * Affiche ou masque le bouton "Envoyer" selon qu'un fichier a été sélectionné ou non
 * via le bouton "Parcourir".
 * 
 * @param object container
 * @returns void
 */
function updateUploadButton(container) 
{
    var browseButton = $(".choose-file", container);
    var sendBtn      = browseButton.siblings(".upload-file");
    
    browseButton.val() ? sendBtn.fadeIn() : sendBtn.hide();
}