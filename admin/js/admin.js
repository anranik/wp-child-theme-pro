/**
 * Admin JavaScript for WP Child Theme Pro
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Generate child theme form submission
        $('#wpchild-generate-form').on('submit', function(e) {
            e.preventDefault();
            
            // Show spinner
            $('#generate-button').prop('disabled', true);
            $(this).find('.spinner').addClass('is-active');
            
            // Get form data
            var formData = $(this).serialize();
            
            // Send AJAX request
            $.ajax({
                url: wpchild_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpchild_generate_child_theme',
                    nonce: wpchild_vars.nonce,
                    parent_theme: $('#parent-theme').val(),
                    child_name: $('#child-name').val(),
                    child_desc: $('#child-desc').val(),
                    child_author: $('#child-author').val(),
                    child_version: $('#child-version').val(),
                    copy_settings: $('#copy-settings').is(':checked') ? 1 : 0,
                    selected_files: getSelectedFiles()
                },
                success: function(response) {
                    // Hide spinner
                    $('#generate-button').prop('disabled', false);
                    $('#wpchild-generate-form').find('.spinner').removeClass('is-active');
                    
                    if (response.success) {
                        // Show success message
                        $('.wpchild-result').show();
                        $('#wpchild-result-message').html('<p>' + response.data.message + '</p>');
                        
                        // Update activate button
                        $('#activate-theme').data('theme', response.data.child_theme);
                    } else {
                        // Show error message
                        alert(response.data || wpchild_vars.generating_error);
                    }
                },
                error: function() {
                    // Hide spinner
                    $('#generate-button').prop('disabled', false);
                    $('#wpchild-generate-form').find('.spinner').removeClass('is-active');
                    
                    // Show error message
                    alert(wpchild_vars.generating_error);
                }
            });
        });
        
        // Parent theme selection change
        $('#parent-theme').on('change', function() {
            var parentTheme = $(this).val();
            
            if (parentTheme) {
                // Show loading
                $('#parent-files-container').html('<div class="wpchild-loading">' + wpchild_vars.generating + '</div>');
                
                // Get parent theme files
                $.ajax({
                    url: wpchild_vars.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'wpchild_list_parent_files',
                        nonce: wpchild_vars.nonce,
                        parent_theme: parentTheme
                    },
                    success: function(response) {
                        if (response.success) {
                            // Display files
                            var files = response.data;
                            var html = '<div class="wpchild-file-list-inner">';
                            
                            html += '<p><label><input type="checkbox" class="select-all-files"> ' + 
                                   'Select/Deselect All</label></p>';
                            
                            // Group files by directory
                            var fileGroups = groupFilesByDirectory(files);
                            
                            // Add files to HTML
                            for (var dir in fileGroups) {
                                html += '<div class="wpchild-file-group">';
                                html += '<h4>' + (dir || 'Root Directory') + '</h4>';
                                html += '<ul>';
                                
                                for (var i = 0; i < fileGroups[dir].length; i++) {
                                    var file = fileGroups[dir][i];
                                    html += '<li><label>' +
                                           '<input type="checkbox" name="selected_files[]" value="' + file + '" ' +
                                           (isCommonFile(file) ? 'checked' : '') + '> ' +
                                           file +
                                           '</label></li>';
                                }
                                
                                html += '</ul>';
                                html += '</div>';
                            }
                            
                            html += '</div>';
                            $('#parent-files-container').html(html);
                            
                            // Select all functionality
                            $('.select-all-files').on('change', function() {
                                $('input[name="selected_files[]"]').prop('checked', $(this).prop('checked'));
                            });
                        } else {
                            // Show error
                            $('#parent-files-container').html('<div class="wpchild-error">' + 
                                                            response.data + '</div>');
                        }
                    },
                    error: function() {
                        // Show error
                        $('#parent-files-container').html('<div class="wpchild-error">' + 
                                                        'Error loading files. Please try again.' + '</div>');
                    }
                });
            } else {
                // Clear file list
                $('#parent-files-container').html('<div class="wpchild-loading">' + 
                                              'Please select a parent theme first...' + '</div>');
            }
        });
        
        // Activate theme button
        $('#activate-theme').on('click', function(e) {
            e.preventDefault();
            
            var theme = $(this).data('theme');
            
            if (!theme) {
                return;
            }
            
            // Show loading
            $(this).prop('disabled', true).text('Activating...');
            
            // Send activation request
            $.ajax({
                url: wpchild_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpchild_activate_theme',
                    nonce: wpchild_vars.nonce,
                    theme: theme
                },
                success: function(response) {
                    if (response.success) {
                        // Reload page
                        window.location.reload();
                    } else {
                        // Show error
                        alert(response.data);
                        $('#activate-theme').prop('disabled', false).text('Activate Child Theme');
                    }
                },
                error: function() {
                    // Show error
                    alert('Error activating theme. Please try again.');
                    $('#activate-theme').prop('disabled', false).text('Activate Child Theme');
                }
            });
        });
        
        // Custom CSS editor in customize page
        if ($('#custom-css-editor').length) {
            var cssEditor = CodeMirror.fromTextArea(document.getElementById('custom-css-editor'), {
                lineNumbers: true,
                mode: 'css',
                theme: 'default',
                lineWrapping: true
            });
            
            // Save custom CSS button
            $('#save-custom-css').on('click', function() {
                // Get CSS
                var css = cssEditor.getValue();
                var theme = $('#child-theme-select').val();
                
                if (!theme) {
                    alert('Please select a child theme.');
                    return;
                }
                
                // Show loading
                $(this).prop('disabled', true);
                $('.wpchild-saving').show();
                
                // Send save request
                $.ajax({
                    url: wpchild_vars.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'wpchild_save_custom_css',
                        nonce: wpchild_vars.nonce,
                        theme: theme,
                        css: css
                    },
                    success: function(response) {
                        // Hide loading
                        $('#save-custom-css').prop('disabled', false);
                        $('.wpchild-saving').hide();
                        
                        if (response.success) {
                            // Show success message
                            $('.wpchild-success').text(response.data).fadeIn().delay(3000).fadeOut();
                        } else {
                            // Show error message
                            alert(response.data);
                        }
                    },
                    error: function() {
                        // Hide loading
                        $('#save-custom-css').prop('disabled', false);
                        $('.wpchild-saving').hide();
                        
                        // Show error message
                        alert('Error saving CSS. Please try again.');
                    }
                });
            });
        }
        
        // Custom JS editor in customize page
        if ($('#custom-js-editor').length) {
            var jsEditor = CodeMirror.fromTextArea(document.getElementById('custom-js-editor'), {
                lineNumbers: true,
                mode: 'javascript',
                theme: 'default',
                lineWrapping: true
            });
            
            // Save custom JS button
            $('#save-custom-js').on('click', function() {
                // Get JS
                var js = jsEditor.getValue();
                var theme = $('#child-theme-select').val();
                
                if (!theme) {
                    alert('Please select a child theme.');
                    return;
                }
                
                // Show loading
                $(this).prop('disabled', true);
                $('.wpchild-saving').show();
                
                // Send save request
                $.ajax({
                    url: wpchild_vars.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'wpchild_save_custom_js',
                        nonce: wpchild_vars.nonce,
                        theme: theme,
                        js: js
                    },
                    success: function(response) {
                        // Hide loading
                        $('#save-custom-js').prop('disabled', false);
                        $('.wpchild-saving').hide();
                        
                        if (response.success) {
                            // Show success message
                            $('.wpchild-success').text(response.data).fadeIn().delay(3000).fadeOut();
                        } else {
                            // Show error message
                            alert(response.data);
                        }
                    },
                    error: function() {
                        // Hide loading
                        $('#save-custom-js').prop('disabled', false);
                        $('.wpchild-saving').hide();
                        
                        // Show error message
                        alert('Error saving JS. Please try again.');
                    }
                });
            });
        }
        
        // Backup functionality
        $('.wpchild-restore-backup').on('click', function(e) {
            e.preventDefault();
            
            var backupFile = $(this).data('backup');
            var nonce = $(this).data('nonce');
            
            if (confirm(wpchildLocalize.restore_confirm)) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wpchild_restore_backup',
                        backup_file: backupFile,
                        nonce: nonce
                    },
                    beforeSend: function() {
                        showLoader();
                    },
                    success: function(response) {
                        hideLoader();
                        if (response.success) {
                            alert(response.data.message);
                            location.reload();
                        } else {
                            alert(response.data.message);
                        }
                    },
                    error: function() {
                        hideLoader();
                        alert(wpchildLocalize.ajax_error);
                    }
                });
            }
        });
        
        $('.wpchild-delete-backup').on('click', function(e) {
            e.preventDefault();
            
            var backupFile = $(this).data('backup');
            var nonce = $(this).data('nonce');
            
            if (confirm(wpchildLocalize.delete_confirm)) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wpchild_delete_backup',
                        backup_file: backupFile,
                        nonce: nonce
                    },
                    beforeSend: function() {
                        showLoader();
                    },
                    success: function(response) {
                        hideLoader();
                        if (response.success) {
                            alert(response.data.message);
                            location.reload();
                        } else {
                            alert(response.data.message);
                        }
                    },
                    error: function() {
                        hideLoader();
                        alert(wpchildLocalize.ajax_error);
                    }
                });
            }
        });
        
        // Backup and restore
        if ($('.backup-theme').length) {
            // Backup theme button
            $('.backup-theme').on('click', function() {
                var theme = $(this).data('theme');
                
                if (!theme) {
                    alert('Please select a theme to backup.');
                    return;
                }
                
                // Show loading
                $(this).prop('disabled', true).text('Backing up...');
                
                // Send backup request
                $.ajax({
                    url: wpchild_vars.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'wpchild_backup_theme',
                        nonce: wpchild_vars.nonce,
                        theme: theme
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            alert(response.data);
                            // Reload the page to show the new backup
                            window.location.reload();
                        } else {
                            // Show error
                            alert(response.data);
                            $('.backup-theme').prop('disabled', false).text('Backup');
                        }
                    },
                    error: function() {
                        // Show error
                        alert('Error backing up theme. Please try again.');
                        $('.backup-theme').prop('disabled', false).text('Backup');
                    }
                });
            });
            
            // Restore backup button
            $(document).on('click', '.restore-backup', function() {
                if (!confirm('Are you sure you want to restore this backup? This will overwrite the existing theme.')) {
                    return;
                }
                
                var backup = $(this).data('backup');
                
                // Show loading
                $(this).prop('disabled', true).text('Restoring...');
                
                // Send restore request
                $.ajax({
                    url: wpchild_vars.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'wpchild_restore_backup',
                        nonce: wpchild_vars.nonce,
                        backup_file: backup
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            alert(response.data);
                            // Reload the page
                            window.location.reload();
                        } else {
                            // Show error
                            alert(response.data);
                            $('.restore-backup').prop('disabled', false).text('Restore');
                        }
                    },
                    error: function() {
                        // Show error
                        alert('Error restoring backup. Please try again.');
                        $('.restore-backup').prop('disabled', false).text('Restore');
                    }
                });
            });
            
            // Delete backup button
            $(document).on('click', '.delete-backup', function() {
                if (!confirm('Are you sure you want to delete this backup?')) {
                    return;
                }
                
                var backup = $(this).data('backup');
                var $row = $(this).closest('tr');
                
                // Show loading
                $(this).prop('disabled', true).text('Deleting...');
                
                // Send delete request
                $.ajax({
                    url: wpchild_vars.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'wpchild_delete_backup',
                        nonce: wpchild_vars.nonce,
                        backup_file: backup
                    },
                    success: function(response) {
                        if (response.success) {
                            // Remove the row
                            $row.fadeOut(300, function() {
                                $(this).remove();
                            });
                        } else {
                            // Show error
                            alert(response.data);
                            $('.delete-backup').prop('disabled', false).text('Delete');
                        }
                    },
                    error: function() {
                        // Show error
                        alert('Error deleting backup. Please try again.');
                        $('.delete-backup').prop('disabled', false).text('Delete');
                    }
                });
            });
        }
        
        /**
         * Helper function to get selected files
         * 
         * @return {Array} Selected files
         */
        function getSelectedFiles() {
            var files = [];
            
            $('input[name="selected_files[]"]:checked').each(function() {
                files.push($(this).val());
            });
            
            return files;
        }
        
        /**
         * Helper function to group files by directory
         * 
         * @param {Array} files List of files
         * @return {Object} Files grouped by directory
         */
        function groupFilesByDirectory(files) {
            var groups = {};
            
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                var dir = '';
                
                // Extract directory
                if (file.indexOf('/') !== -1) {
                    dir = file.substr(0, file.lastIndexOf('/'));
                }
                
                // Create group if it doesn't exist
                if (!groups[dir]) {
                    groups[dir] = [];
                }
                
                // Add file to group
                groups[dir].push(file);
            }
            
            return groups;
        }
        
        /**
         * Helper function to check if a file is commonly included in child themes
         * 
         * @param {string} file File path
         * @return {boolean} Whether the file is commonly included
         */
        function isCommonFile(file) {
            var commonFiles = [
                'functions.php',
                'style.css',
                'screenshot.png',
                'header.php',
                'footer.php'
            ];
            
            // Check if the file name is in the list of common files
            for (var i = 0; i < commonFiles.length; i++) {
                if (file === commonFiles[i] || file.endsWith('/' + commonFiles[i])) {
                    return true;
                }
            }
            
            return false;
        }
        
        /**
         * Helper function to show loader
         */
        function showLoader() {
            $('.wpchild-loader').show();
        }
        
        /**
         * Helper function to hide loader
         */
        function hideLoader() {
            $('.wpchild-loader').hide();
        }
        
    });
    
})(jQuery);
