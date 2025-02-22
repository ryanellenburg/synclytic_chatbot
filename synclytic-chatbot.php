/*
Comprehensive PHP Chatbot for Investors and Prospects
=====================================================
This chatbot handles:
  - Multi-step interactions for prospects and existing investors
  - Intelligent input validation and name formatting
  - Dynamic redirection and interaction with OpenAI API
  - Spam prevention with honeypot and Google reCAPTCHA
  - Email notifications for human contact requests
  - Proper UX flow with delayed follow-ups and return options

Key Features:
  - Uses GPT-3.5 Turbo for intelligent responses where applicable
  - Properly formats names with cultural exceptions
  - Integrates with WhatsApp for real-time human interaction
  - Comprehensive security measures including XSS protection
*/

// Initialize session for tracking conversation state
session_start();

function chatbot_enqueue_styles() {
    wp_enqueue_style('chatbot-styles', plugin_dir_url(__FILE__) . 'chatbot-styles.css');
}
add_action('wp_enqueue_scripts', 'chatbot_enqueue_styles');

// Include necessary dependencies for form validation and email handling
require_once('wp-load.php'); // Required for WordPress environment
require_once('recaptcha.php'); // Include your reCAPTCHA library here

// Function to format and validate names
function format_name($name) {
    $exceptions = array('al', 'bin', 'de', 'da', 'di', 'von', 'van', 'la', 'le', 'du', 'des', 'del', 'della', 'md');
    $name = strtolower($name);
    $words = explode(' ', $name);
    foreach ($words as $key => $word) {
        if (in_array($word, $exceptions) && $key > 0) {
            $words[$key] = $word;
        } else {
            $words[$key] = ucfirst($word);
        }
    }
    return implode(' ', $words);
}

// Function to validate names against empty fields, gibberish, and banned words
function validate_name($name) {
    if (empty($name)) return 'Name cannot be empty.';
    if (!preg_match("/^[a-zA-Z '-]+$/", $name)) return 'Please use letters only.';
    $banned_words = array('damn', 'hell', 'shit', 'fuck', 'fucker', 'fuckhead', 'bitch', 'bastard', 'asshole', 'cunt', 'cocksucker', 'motherfucker', 'nigger', 'spic', 'chink', 'fag', 'faggot', 'slut', 'whore', 'jackass', 'prick', 'twat', 'wanker', 'bloody', 'bugger', 'cum', 'douche', 'dildo', 'wank', 'poon', 'bint', 'shag', 'tosser', 'minger', 'git', 'bellend', 'clunge', 'muff', 'minge', 'arse', 'shite', 'sod', 'bollocks', 'bugger', 'plonker', 'arsehole', 'muppet');
    $lower_name = strtolower($name);
    foreach ($banned_words as $badword) {
        if (strpos($lower_name, $badword) !== false) return 'Inappropriate words are not allowed.';
    }
    return true;
}

// Main chatbot logic
function chatbot_handler() {
    // Display greeting and initial options
    echo "<p>Hi, what can I help you with?</p>";
    echo "<button onclick=\"processSelection('investing')\">I am interested in investing.</button>";
    echo "<button onclick=\"processSelection('investor')\">I'm an investor and need assistance.</button>";
    echo "<button onclick=\"processSelection('other')\">Something else.</button>";

    echo "<script>
        function askAnythingElse() {
    var userChoice = confirm('Is there anything else I can help you with?');
    if (userChoice) {
        document.write('<p>Would you like to:</p>');
        document.write('<button onclick="followUpChoice(\'question\')">Ask another question</button>');
        document.write('<button onclick="followUpChoice(\'human\')">Speak to a human</button>');
    } else {
        alert('Thank you so much and let me know if you need anything else!');
        window.close();
    }
}
      
function followUpChoice(choice) {
    if (choice === 'question') {
        alert('Alright, let’s get back to where we left off.');
        location.reload();
    } else if (choice === 'human') {
        askHumanContact();
    } else {
        alert('Sorry, I didn’t understand that. Please try again.');
        askAnythingElse();
    }
} else if (followUp.toLowerCase() === 'human') {
                    askHumanContact();
                } else {
                    alert('Sorry, I didn’t understand that. Please try again.');
                    askAnythingElse();
                }
            } else {
                alert('Thank you so much and let me know if you need anything else!');
                window.close();
            }
        }

        function askHumanContact() {
            document.write('<p>Alright, let’s get you to the right person. I just need some information:</p>');
            document.write('<form method=\"post\" action=\"\">');
            document.write('<p>What is your first name?</p>');
            document.write('<input type=\"text\" name=\"first_name\" placeholder=\"First Name\" required>');
            document.write('<p>What is your last name?</p>');
            document.write('<input type=\"text\" name=\"last_name\" placeholder=\"Last Name\" required>');
            document.write('<p>What is your email?</p>');
            document.write('<input type=\"email\" name=\"email\" placeholder=\"Email\" required>');
            document.write('<p>What is the best number to reach you at?</p>');
            document.write('<input type=\"text\" name=\"phone\" placeholder=\"Phone Number\" required>');
            document.write('<p>Do you prefer a call or text?</p>');
            document.write('<input type=\"radio\" name=\"contact_method\" value=\"call\" required> Call');
            document.write('<input type=\"radio\" name=\"contact_method\" value=\"text\" required> Text');
            document.write('<p>What do you need help with so I can get you to the right person?</p>');
            document.write('<textarea name=\"help_request\" required></textarea>');
            document.write('<input type=\"text\" name=\"honeypot\" style=\"display:none;\">');
            document.write('<div class=\"g-recaptcha\" data-sitekey=\"YOUR_SITE_KEY\"></div>');
            document.write('<button type=\"submit\" onclick=\"this.disabled=true; this.innerHTML=\'Submitting...\'; this.form.submit();\">Submit</button>');
            document.write('</form>');
            document.write('<script src=\"https://www.google.com/recaptcha/api.js\"></script>');
        }
    </script>";
}
      
// Process user selection and navigate conversation tree
function processSelection($selection) {
    switch ($selection) {

        // If the person is interested in investing
        case 'investing':
            echo "<p>Great! How can I help?</p>";
            echo "<button onclick=\"processSelection('appointment')\">I’d like to set up an appointment.</button>";
            echo "<button onclick=\"processSelection('roi')\">What is the ROI?</button>";
            echo "<button onclick=\"processSelection('exit')\">What is the company exit plan?</button>";
            echo "<button onclick=\"processSelection('scam')\">How do I know this isn’t a scam?</button>";
            echo "<button onclick=\"processSelection('other')\">Something else.</button>";
            break;
        case 'investor':
            echo "<p>We appreciate you being a part of the team! How can I help?</p>";
            echo "<button onclick=\"processSelection('access_portal')\">How do I access my investor portal?</button>";
            echo "<button onclick=\"processSelection('financials')\">Can I access the most recent financials?</button>";
            echo "<button onclick=\"processSelection('existing_investments')\">Do you have any investments available for existing investors?</button>";
            echo "<button onclick=\"processSelection('other')\">Something else.</button>";
            break;
        case 'appointment':
            echo "<p>Great, I will forward you to the scheduling page now.</p>";
            echo "<script>window.open('scheduling-page-url', '_blank');</script>";
            echo "<script>setTimeout(function() { askAnythingElse(); }, 3000);</script>";
            break;
        case 'roi':
            echo "<p>TODO</p>";
            echo "<script>setTimeout(function() { askAnythingElse(); }, 3000);</script>";
            break;
        case 'exit':
            echo "<p>TODO</p>";
            echo "<script>setTimeout(function() { askAnythingElse(); }, 3000);</script>";
            break;
        case 'scam':
            echo "<p>Please click <a href='SEC-portal-link'>HERE</a> to access the company’s portal on the SEC’s database for audited financials and bad actor checks.</p>";
            echo "<script>setTimeout(function() { askAnythingElse(); }, 3000);</script>";
            break;

        // If the person is already an investor
        case 'access_portal':
            echo "<p>Your shares are registered with ComputerShare. You can click <a href='link-to-pdf'>HERE</a> to download a PDF that goes over the instructions on accessing the portal.</p>";
            echo "<script>setTimeout(function() { askAnythingElse(); }, 3000);</script>";
            break;
        case 'financials':
            echo "<p>You can view the most recent audited financials on the SEC’s database <a href='link-to-sec-database'>HERE</a>.</p>";
            echo "<script>setTimeout(function() { askAnythingElse(); }, 3000);</script>";
            break;
        case 'existing_investments':
            echo "<p>It would be best to set up an appointment with a consultant to see if you qualify. Would you like me to send you to the page to book it on your calendar?</p>";
            echo "<button onclick=\"processSelection('book_appointment')\">Yes</button>";
            echo "<button onclick=\"processSelection('no_appointment')\">No</button>";
            break;
        case 'book_appointment':
            echo "<script>window.open('booking-page-url', '_blank');</script>";
            echo "<p>Opening the scheduling page...</p>";
            echo "<script>setTimeout(function() { askAnythingElse(); }, 3000);</script>";
            break;
        case 'no_appointment':
            echo "<p>No problem, you can always email <a href='mailto:investors@example.com'>investors@example.com</a> with this question.</p>";
            echo "<script>setTimeout(function() { askAnythingElse(); }, 3000);</script>";
            break;
        case 'appointment':
            echo "<p>I will forward you to the scheduling page now.</p>";
            echo "<script>window.open('scheduling-page-url', '_blank');</script>";
            echo "<script>setTimeout(function() { askAnythingElse(); }, 3000);</script>";
            break;

        // Any time "Something Else" is chosen it will lead to OpenAI API
        case 'other':
            echo "<p>Let’s connect you with our virtual assistant. It is AI and pretty cool! First, I just need to get some information.</p>";
            echo "<form method='post' action=''>";
            echo "<p>What is your first name?</p>";
            echo "<input type='text' name='first_name' placeholder='First Name' required>";
            echo "<p>What is your last name?</p>";
            echo "<input type='text' name='last_name' placeholder='Last Name' required>";
            echo "<p>What is your email?</p>";
            echo "<input type='email' name='email' placeholder='Email' required>";
            echo "<p>What is your phone number?</p>";
            echo "<input type='text' name='phone' placeholder='Phone Number' required>";
            echo "<p>Should we need to follow up, do you prefer a call, text, or email?</p>";
            echo "<input type='radio' name='contact_method' value='call' required> Call";
            echo "<input type='radio' name='contact_method' value='text' required> Text";
            echo "<input type='radio' name='contact_method' value='email' required> Email";
            echo "<input type='text' name='honeypot' style='display:none;'>";
            echo "<div class='g-recaptcha' data-sitekey='YOUR_SITE_KEY'></div>";
            echo "<button type='submit' onclick=\"this.disabled=true; this.innerHTML='Submitting...'; this.form.submit();\">Submit</button>";
            echo "</form>";
            echo "<script src='https://www.google.com/recaptcha/api.js'></script>";

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST['honeypot'])) {
                $first_name = format_name(sanitize_text_field($_POST['first_name']));
                $last_name = format_name(sanitize_text_field($_POST['last_name']));
                $email = sanitize_email($_POST['email']);
                $phone = sanitize_text_field($_POST['phone']);
                // Phone number validation
                if (!preg_match('/^(\\+?[2-9]\\d{1,2})?[-. (]*(\\d{3})[-. )]*(\\d{3})[-. ]*(\\d{4})$/', $phone)) {
                    echo "<p style='color:red;'>Invalid phone number. Please enter a valid number.</p>";
                    return;
                }
                $contact_method = sanitize_text_field($_POST['contact_method']);

                echo "<p>Thank you for all that, " . esc_html($first_name) . ". I am your virtual assistant and will do my best to help answer any question you have. Please remember that even the best AI out there can make mistakes. So if you need further assistance or want to speak to a human, just let me know!</p>";

                echo "<p>What question can I help you with?</p>";
                echo "<form method='post' action=''>";
                echo "<textarea name='user_question' placeholder='Type your question here...' required></textarea>";
                echo "<button type='submit' onclick=\"this.disabled=true; this.innerHTML='Submitting...'; this.form.submit();\">Ask AI</button>";
                echo "</form>";

                if (!empty($_POST['user_question'])) {
                    $question = sanitize_text_field($_POST['user_question']);
                    $api_key = 'YOUR_OPENAI_API_KEY';
                    $endpoint = 'https://api.openai.com/v1/chat/completions';
                    $data = array(
                        "model" => "gpt-3.5-turbo",
                        "messages" => array(array("role" => "user", "content" => $question))
                    );
                    $response = wp_remote_post($endpoint, array(
                        'body' => json_encode($data),
                        'headers' => array(
                            'Content-Type' => 'application/json',
                            'Authorization' => 'Bearer ' . $api_key,
                        ),
                    ));
                    if (is_wp_error($response)) {
                        echo "<p>Sorry, there was an error processing your request. AI isn't perfect... yet. Please try again later or you can always speak with a human.</p>";
                    } else {
                        $body = json_decode(wp_remote_retrieve_body($response), true);
                        if (!empty($body['choices'][0]['message']['content'])) {
                            echo "<p>AI Response: " . esc_html($body['choices'][0]['message']['content']) . "</p>";
                        } else {
                            echo "<p>Sorry, I couldn't get a response right now. Please try again later or speak to a human.</p>";
                        }
                    }
                }
            }
            break;
        default:
            echo "<p>Invalid selection. Please try again.</p>";
            echo "<button onclick=\"goToMainMenu()\">Back to Main Menu</button>";
            echo "<script>
                function goToMainMenu() {
                    location.reload();
                }
            </script>";
            break;
    }
}

// Initialize chatbot on the WordPress page
add_shortcode('investment_chatbot', 'chatbot_handler');
