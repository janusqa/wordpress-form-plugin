<?php if (!defined('ABSPATH')) exit; ?>
<?php if (get_plugin_options('simple_contact_form_active')) : ?>
    <div style="width:25%">
        <div id="form_submission_status" style="display:none; padding:0.5em; margin-block-end:0.5em; border-radius:0.5em"></div>
        <form id="simple_contact_form" style="display:flex; flex-direction:column; gap: 2px;">
            <?php wp_nonce_field('wp_rest'); ?> <!-- portect form from bots -->
            <label>Name:</label>
            <input type="text" name="name">
            <label>Email:</label>
            <input type="text" name="email">
            <label>Phone:</label>
            <input type="text" name="phone">
            <label>Message:</label>
            <textarea name="message"></textarea>
            <button type="submit" style="margin-block-start:0.5em">Submit</button>
        </form>
    </div>

    <script>
        const form_submission_status = document.getElementById("form_submission_status");
        const simple_contact_form = document.getElementById("simple_contact_form");

        simple_contact_form.addEventListener("submit", process_form);

        function process_form(event) {
            event.preventDefault();

            const formData = new FormData(event.target);
            fetch("<?php echo get_rest_url(null, "v1/simple-contact-form/process_form"); ?>", {
                method: "POST",
                body: formData
            }).then(response => {
                if (!response.ok) {
                    if (response.status === 404) throw new Error('404, Not found');
                    if (response.status === 500) throw new Error('500, internal server error');
                    if (response.status === 400) throw new Error('400, Bad Request');
                    throw new Error(response.status);
                }
                return response.json();
            }).then(data => {
                simple_contact_form.style.display = "none";
                form_submission_status.textContent = data.message;
                form_submission_status.style.background = "green";
                form_submission_status.style.color = "white";
                form_submission_status.style.display = "block";
            }).catch(error => {
                form_submission_status.textContent = `Simple Contact Form Error: ${error.message}`;
                form_submission_status.style.background = "red";
                form_submission_status.style.color = "white";
                form_submission_status.style.display = "block";
            });
        }
    </script>
<?php else : ?>
    <p>This form is not active
    <p>
    <?php endif ?>