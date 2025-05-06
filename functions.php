<?php
// ini_set("include_path", '/home/dh_dyudg9/ashlandwall.com/php:' . ini_get("include_path") );
//****************************************************************************************

function truncate_text($text, $max_length = 55) {
    return (strlen($text) > $max_length) ? substr($text, 0, $max_length) . '...' : $text;
}

function getContactsByTagWithPagination($pdo, $tagId, $pagination_vars)
{
    $sql = "
        SELECT c.contact_id, c.first_name, c.last_name, c.email
        FROM contacts c
        JOIN tags t ON c.tag_id = t.tag_id
        WHERE t.tag_id = :tag_id
        ORDER BY c.first_name, c.last_name
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['tag_id' => $tagId]);

    $contacts = [];
    foreach ($stmt as $row) {
        $contacts[] = $row;
    }

    return array_slice($contacts, ($pagination_vars['current_page'] - 1) * $pagination_vars['records_per_page'], $pagination_vars['records_per_page']);
}
function getContactsCountByTag($pdo, $tagId)
{ //test completed and active
    $sql = "SELECT COUNT(*) as count 
            FROM contacts c 
            JOIN contact_tags ct ON c.contact_id = ct.contact_id 
            WHERE ct.tag_id = :tag_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['tag_id' => $tagId]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}
function getContactsFromQuery($query, $params = [])
{
    global $pdo;

    try {
        $stmt = $pdo->prepare($query);

        if (!empty($params)) {
            $stmt->execute($params);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        return [];
    }
}


function getContactsInLastDays($days)
{
    global $pdo;
    $sql = "SELECT COUNT(*) FROM contacts WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$days]);
    return $stmt->fetchColumn();
}
function getTotalTags()
{//test created
    global $pdo;
    $sql = "SELECT COUNT(*) FROM tags";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchColumn();
}
function updateEmailsTable($email_id, $response, $count)
{
    //this is fired after successfull sending of email campaign via sendGrid API in process_send_multiple_emails.php
    global $pdo;
    // error_log('count of emails inside function: ' . $count);
    $sql = "UPDATE emails SET sent_date = CURRENT_TIMESTAMP,
                response_code = ?,
                emails_count = ?,
                error_message = ?
            WHERE email_id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $response->statusCode(),
        $count,
        $response->body(),
        $email_id
    ]);

    return true;
}
// Add this to functions.php
function getTagIdByName($pdo, $tagName)
{
    $sql = "SELECT tag_id FROM tags WHERE name = :name LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['name' => $tagName]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['tag_id'] : null;
}

function getListName($list_id)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT list_name FROM lists WHERE list_id = ?");
        $stmt->execute([$list_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return $result['list_name'];
        } else {
            return "List Not Found";
        }
    } catch (PDOException $e) {
        error_log("Error in getListName: " . $e->getMessage());
        return "Error Getting List Name";
    }
}
function getPaginationVars($total_records, $records_per_page, $current_page = 1)
{
    return [
        'total_records' => $total_records,
        'total_pages' => ceil($total_records / $records_per_page),
        'current_page' => $current_page,
        'records_per_page' => $records_per_page,
        'offset' => ($current_page - 1) * $records_per_page
    ];
}

function buildPagination($pagination_vars)
{
    $html = '';
    if ($pagination_vars['total_pages'] > 1) {
        $html .= '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';

        // Previous button
        if ($pagination_vars['current_page'] > 1) {
            $html .= '<li class="page-item"><a class="page-link" href="#" data-page="' . ($pagination_vars['current_page'] - 1) . '">Previous</a></li>';
        }

        // Page numbers
        for ($i = 1; $i <= $pagination_vars['total_pages']; $i++) {
            $active = ($pagination_vars['current_page'] == $i) ? 'active' : '';
            $html .= '<li class="page-item ' . $active . '"><a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a></li>';
        }

        // Next button
        if ($pagination_vars['current_page'] < $pagination_vars['total_pages']) {
            $html .= '<li class="page-item"><a class="page-link" href="#" data-page="' . ($pagination_vars['current_page'] + 1) . '">Next</a></li>';
        }

        $html .= '</ul></nav>';
    }
    return $html;
}

// And update mapForSendGrid to handle multiple records:
function mapForSendGrid($results)
{
    // error_log("Results received in mapForSendGrid: " . print_r($results, true));
    $tos = array();
    foreach ($results as $row) {
        $tos[$row['email']] = $row['first_name'];
    }
    return $tos;
}
//function that echos the current day and date
function echoCurrentDate()
{//test in place
    echo date('l F j, Y');
}

function getTagsData($pdo, $pagination_vars, $search = '')
{
    // $query = "SELECT * FROM tags WHERE name LIKE ? ORDER BY name LIMIT ? OFFSET ?";
    $query = "SELECT * FROM tags WHERE name LIKE ? ORDER BY created_at DESC, name LIMIT ? OFFSET ?";

    $stmt = $pdo->prepare($query);
    $searchTerm = "%" . $search . "%";
    $stmt->bindValue(1, $searchTerm, PDO::PARAM_STR);
    $stmt->bindValue(2, $pagination_vars['records_per_page'], PDO::PARAM_INT);
    $stmt->bindValue(3, $pagination_vars['offset'], PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

//not sure if this is being used, as it just returns how many tags match the search string. it will always be 1
function getTagsCount($pdo, $search = '')
{
    $query = "SELECT COUNT(*) FROM tags WHERE name LIKE ?";
    $stmt = $pdo->prepare($query);
    $searchTerm = "%" . $search . "%";
    $stmt->execute([$searchTerm]);
    return $stmt->fetchColumn();
    
}

function buildTagsTable($tags)
{//test live
    $html = '<div class="table-responsive">';
    $html .= '<table class="table table-hover" id="tagsTable">';
    $html .= '<thead>
                <tr>
                    <th width="30"><input type="checkbox" class="form-check-input"></th>
                    <th>Id</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Number of people</th>
                    <th></th>
                </tr>
            </thead>';
    $html .= '<tbody>';

    foreach ($tags as $tag) {
        $html .= "<tr>";
        $html .= "<td><input type='checkbox' class='form-check-input'></td>";
        $html .= "<td>" . htmlspecialchars($tag['tag_id']) . "</td>";
        $html .= "<td>" . htmlspecialchars($tag['name']) . "</td>";
        $html .= "<td>" . htmlspecialchars($tag['category'] ?? '') . "</td>";
        $html .= "<td></td>";
        $html .= "<td><button class='show-number-btn'>Show Number</button></td>";
        $html .= "</tr>";
    }

    $html .= '</tbody></table></div>';
    return $html;
}
function getTotalContacts()
{ //test is live
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) FROM contacts");
    return $stmt->fetchColumn();
}
function getContacts($limit, $offset)
{//test is live
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM contacts 
    					   ORDER BY first_name LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTags()
{//test live
    global $pdo;
    $stmt = $pdo->prepare("SELECT t.name, t.tag_id, COUNT(ct.contact_id) as count 
							FROM tags t
							LEFT JOIN contact_tags ct ON t.tag_id = ct.tag_id
							GROUP BY t.tag_id, t.name
							ORDER BY t.created_at desc
							");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function login_check(){    

    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }


        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
        exit;
        }
}


function redirect($url)
{
    header("Location: $url");
    exit();
}
function console_log($data)
{
    // Check for JSON encoding errors
    $json_data = json_encode($data);
    if (json_last_error() != JSON_ERROR_NONE) {
        $json_data = json_encode("JSON Encoding Error: " . json_last_error_msg());
        // logAction($user_id, 7);
    }

    // Output data to the JavaScript console
    echo "<script>console.log('PHP debugger:', " . $json_data . ");</script>";
}

function getFliers($pdo)
{
    $stmt = $pdo->prepare(
    "SELECT * FROM events
                            WHERE (featured = 1)
                            LIMIT 100");
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $events;
}



function lastUpdated()
{
    global $pdo; // Assuming $pdo is your PDO connection object

    // Ensure the session is started before using session variables
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Ensure user is logged in before attempting to update
    if (isset($_SESSION['user_email'])) {
        $user = $_SESSION['user_email'];

        try {
            // Prepare an SQL statement
            $stmt = $pdo->prepare("UPDATE users SET last_updated = NOW() WHERE user_email = :user");

            // Bind parameters
            $stmt->bindParam(':user', $user, PDO::PARAM_STR);

            // Execute the prepared statement
            $stmt->execute();

            // Code to update last update time for user
            return true;
        } catch (PDOException $e) {
            die("Error: updating lastupdate time " . $e->getMessage());
        }
    } else {
        // Handle case WHERE user_email is not logged in or session is not set
        // This could involve redirecting to a login page, logging an error, etc.
        die("User not logged in.");
    }
}


//********************************************************************************************************

function MG_sendMail($fromEmail, $toEmail, $subject, $message)
{
    //uses mailgun API to send all emails

    # Instantiate the client.
    $mgClient = Mailgun::create(MG_API_KEY, MG_API_HOSTNAME);
    $domain = MG_API_URL;
    $params = array(
        'from'    => $fromEmail,
        'to'      => $toEmail,
        'subject' => $subject,
        'text'    => $message
    );


    try {
        //try and send email via Mailgun API
        $results = $mgClient->messages()->send($domain, $params);
    } catch (Exception $e) {
        error_log('MG_sendMail error: ' . $e->getMessage());
    }
} //end MG_Mailsend function
//******************************************************************************************
//**********************************************************************************************************

function sendContactForm($name, $email, $message)
{
    $newMessage = '';

    $subject = " contact form Message from " . SITE_DOMAIN_NAME . "!";
    $newMessage .=    "Name: " . $name . "\n Email: " . $email . "\n Message: \n\r" . $message;

    try {
        //send email via Mailgun API
        //sends from site address, to admin user email, notifying of new contact form message
        MG_SendMail(SITE_EMAIL, ADMIN_EMAIL, $subject, $newMessage);
        return true; // Indicate success
    } catch (Exception $e) {
        error_log($e->getMessage()); // Log error message
        return false; // Indicate failure
    }
} // end sendcontactform
//*************************************************************************************************************************
function generate_new_password($email)
{
    global $pdo;

    // Generate a new 7-character password
    $password = substr(md5(uniqid(rand())), 0, 7);

    //hash it with bcrypt for the database
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into the db
    $statement = "UPDATE users SET user_password = ? WHERE user_email = ? ";
    $results = $pdo->prepare($statement);
    $results->execute([$hashed_password, $email]);


    $message = "Dear $email,\r\n\r\n";
    $message .= "A request has been made to reset your password on " . SITE_DOMAIN_NAME . "\r\n";
    $message .= "Your password has been reset.\r\n";
    $message .= "Your Username is your email: $email\r\n\r\n";
    $message .= "Your new password is:\r\n";
    $message .= "---------------------------\r\n";
    $message .= "        $password      \r\n";
    $message .= "---------------------------\r\n\r\n";
    $message .= "Please login with the above password at " . SITE_DOMAIN_NAME . "'s website\r\n\r\n";
    $message .= "Once logged in, you can always go to ACCOUNT and change password to whatever you like.\r\n";
    $message .= "Thank you.\r\n";
    $message .= SITE_DOMAIN_NAME . " \r\n\r\n";

    $subject = "Password reset from " . SITE_DOMAIN_NAME . " Site";

    try {
        //send email via Mailgun API
        //sends from site email address, to admin user email, notifying of new login
        MG_SendMail(SITE_EMAIL, $email, $subject, $message);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
} //end generate new password

function changePasswordEmail($password)
{
    if (isset($_SESSION['user_email'])) {
        $email = $_SESSION['user_email'];
    }

    try {

        $message = "Dear $email,\r\n\r\n";
        $message .= "A request has been made to reset your password on " . SITE_DOMAIN_NAME . "\r\n";
        $message .= "Your password has been reset.\r\n";
        $message .= "Your Username is your email: $email.\r\n\r\n";
        $message .= "Your new password is:\r\n";
        $message .= "---------------------------\r\n";
        $message .= "        $password      \r\n";
        $message .= "---------------------------\r\n\r\n";
        $message .= "Please use the new password above for future logins to " . SITE_DOMAIN_NAME . " website\r\n\r\n";
        $message .= "Thank you.\r\n";
        $message .= SITE_DOMAIN_NAME . " \r\n\r\n";

        $subject = SITE_DOMAIN_NAME . " Password Change Notification";
        $body = $message;

        //send email via Mailgun API
        //sends from site email address, to admin user email, notifying of new login
        MG_SendMail(SITE_EMAIL, $email, $subject, $message);
    } catch (Exception $e) {
        echo $e->getMessage();
        error_log('ChangePasswordEmail function error: ' . $e->getMessage());
    }
} //end changePasswordEmail
//************************************************************************************
//**********************************************************************************************************

function newLoginMail()
{

    $email = $_SESSION["user_email"];

    if ($email == 'steelpulsefan@gmail.com') {
        return true;
    } else {

        $subject = $email . " just logged back into " . SITE_DOMAIN_NAME . " cool!";

        $message = "Beloved " . ADMIN_NAME . ",\r\n\r\n";
        $message .= "A beloved user just logged back into your " . SITE_DOMAIN_NAME . " site.\n\nUser Email: " . $email;
        $message .= "\r\n\r\n\r\nBlessings.\r\n";
        $message .= SITE_DOMAIN_NAME . " \r\n\r\n";
        $message .= " When receiving THESE user login emails, becomes too much, simply comment out the function newloginmail() in loguser() function, in functions.php, and they will stop. \r\n\r\n";

        //send email via Mailgun API
        //sends from site email address, to admin user email, notifying of new login
        MG_SendMail(SITE_EMAIL, ADMIN_EMAIL, $subject, $message);
    }
} // end newloginmail
//*************************************************************************************************************************
function newRegistrationEmail($email)
{
    //send an email to site admin emal whenever a new user subscribes to site

    # Include the Autoloader (see "Libraries" for install instructions)


    $subject = $email . " just REGISTERED to " . SITE_DOMAIN_NAME . " Wohoooo!";

    $message = "Beloved " . ADMIN_NAME . ",\r\n\r\n";
    $message .= "We have a NEW registration to your " . SITE_DOMAIN_NAME . " site.\n\nUser Email: " . $email;
    $message .= "\r\n\r\n\r\nBless Sings.\r\n";
    $message .= SITE_DOMAIN_NAME . " \r\n\r\n";
    $message .= "*** When receiving THESE new registration emails, becomes too much, simply comment out the function newRegistrationEmail() newhasheduser function in functions.php, and they will stop. \r\n\r\n";


    //send email via Mailgun API
    //sends from site email address, to admin user email, notifying of new login
    MG_SendMail(SITE_EMAIL, ADMIN_EMAIL, $subject, $message);
} // end loginEmail
//**********************************************************************************************************
function cancelSubscriptionEmail($sid)
{
    global $pdo;
    //send an email to site admin emal whenever a new user subscribes to site

    //get user email from subscrID
    $selectsql = "SELECT * from users WHERE subscrID = ? LIMIT 1";
    $results = $pdo->prepare($selectsql);
    $results->execute([$sid]);
    $row = $results->fetch();
    $user = $row['user_email'];

    $subject = $user . " A subscription was cancelled at: " . SITE_DOMAIN_NAME;
    $message = "Hi,\n\nA User has cancelled their subscription at: " . SITE_DOMAIN_NAME . " site\n\nUser Email: " . $user;


    //send email via Mailgun API
    //sends from site email address, to admin user email, notifying of new login
    MG_SendMail(SITE_EMAIL, ADMIN_EMAIL, $subject, $message);
} // end loginEmail
//********************************************************************************************************
//********************************************************************************************************
function emailMailChimpError($response, $email)
{

    $to = "steelpulsefan@gmail.com";
    $subject = "Error while adding a registration email to Mailchimp list";
    $body = "Hi,\n\nA new user just registered on PersonalDreamJournal.com\n\nUser name: " . $email . ".\n\n And here is the errorthe error:\n\n\n" . $response . " So this new registration was not added to our Mailchimp list, \n\n so we will have to add it manually. So take due notice there of and conduct your self accordingly.";
    if (mail($to, $subject, $body)) {
        return true;
    } else {
        return false;
    }
} // end newregmail

function showAlerts($get)
{

    // if (isset($get['created']) && ($get['created'] == true)) {
    // echo '<span class="alert alert-success mt-3 p-auto" role="alert">New user successfully created!</span>';
    // }
    if (isset($get['edit']) && ($get['edit'] == true)) {
        echo '<span class="alert alert-success mt-3 p-auto" role="alert">User updated successfully!</span>';
    }
    if (isset($get['subscribed']) && ($get['subscribed'] == true)) {
        echo '<span class="alert alert-success  mt-3 p-auto" role="alert">You are now Registered & Subscribed.</span>';
    }
    if (isset($get['card']) && ($get['card'] == false)) {
        echo '<span class="alert alert-success  mt-3 p-auto " role="alert">Sorry, Invalid Card Details!</span>';
    }
    if (isset($get['closed']) && ($get['closed'] == true)) {
        echo '<span class="alert alert-success  mt-3 p-auto " role="alert">Your Account has been successfully CLOSED! 🙏</span>';
    }


    if (isset($get['join']) && ($get['join'] == "failed")) {
        $amt = getBoard($get['amt']);
        echo "<span class=\"alert alert-danger  mt-3 p-auto \" role=\"alert\">You attempted to join the " . $amt . " list, which is not possible right now! No one on the list. Invite some friends, and or check back later :-)</span>";
    }
}
//***************************************************************************************************

function loginAction($id, $a)
{
    global $pdo;
    //when user logs in , lets add an entry to the log file..
    $admin = $id;
    $ip = $_SERVER['REMOTE_ADDR'];
    $useragent = $_SERVER['HTTP_USER_AGENT'];
    $action = $a;


    $sql = "INSERT INTO `log_activity` (admin, user_id, ip, useragent, action) VALUES (?,?,?,?,?)";
    $results = $pdo->prepare($sql);
    $results->execute([$admin, null, $ip, $useragent, $action]);
    // code to update last update time for user
} // end loguser
//***************************************************************************************************

function logAction($user_id, $a)
{
    global $pdo;
    //when user logs in , lets add an entry to the log file..

    $sql = "INSERT INTO `log_activity` (user_id, action) VALUES (?,?)";
    $results = $pdo->prepare($sql);
    $results->execute([$user_id, $a]);

    // see action-codes.txt for codes list


} // end loguser
//***************************************************************************************************

//****************************************************************************************
function loguser()
{
    global $pdo;

    //when user logs in , lets add an entry to the log file..
    $user = $_SESSION['user_email'];

    $ip = $_SERVER['REMOTE_ADDR'];

    $useragent = $_SERVER['HTTP_USER_AGENT'];


    try {
        // Prepare an SQL statement
        $stmt = $pdo->prepare("INSERT INTO log (user_email, ip, useragent) VALUES (:user, :ip, :useragent)");

        // Bind parameters
        $stmt->bindParam(':user', $user, PDO::PARAM_STR);
        $stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
        $stmt->bindParam(':useragent', $useragent, PDO::PARAM_STR);

        // Execute the prepared statement
        $stmt->execute();


        lastupdated();
        newloginmail();
        return true;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} // end loguser



function checkuserexist($user)
{//test completed
    global $pdo; // Use the PDO connection

    $name = $user['email'];

    // Use a prepared statement to safely check if the user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$name]);

    // Check if a user with the provided email exists in the database
    if ($stmt->rowCount() >= 1) {
        return true;
    }
    return false;
}
//end checkuserexist //*******************************************************************************************

//***************************************************************************************************************************
//*******************************************************************************************
function addnewhasheduser($user)
{
    global $pdo; // Use the PDO connection

    session_start();

    $_SESSION['user_email'] = $user['email'];
    $_SESSION['password'] = $password = $user['password'];

    // Hash the password using bcrypt
    $password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user into users table using prepared statements
    $stmt = $pdo->prepare("INSERT INTO users (user_email, user_password, last_updated, create_date) VALUES (?, ?, NOW(), NOW())");

    if ($stmt->execute([$user['email'], $password])) {

        $user_id = $pdo->lastInsertId();  // Get the last inserted ID

        $_SESSION['user_id'] = $user_id;
        $_SESSION["user_email"] = $user['email'];
        $_SESSION["did_admin"] = false;
        // Log the 1 for logged in
        logAction($user_id, 0);
        logAction($user_id, 1);

        newRegistrationEmail($user['email']);

        header("location: ./index.php");
        exit();
    } else {
        die("Error: While adding new hashed user in functions");
    }

    return false;
}
//*******************************************************************************************

function getCategoryName($id)
{
    global $pdo;
    try {
        $selectsql = "SELECT category_name FROM event_categories where id = :id";
        $stmt = $pdo->prepare($selectsql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($results) {
            return $results['category_name'];
        } else {
            throw new Exception("Category ID not found");
        }
    } catch (Exception $e) {
        // Log error or handle as needed
        error_log($e->getMessage());
        return null;
    }
}


function getid($user)
{

    $results = $c1->query(" SELECT profile_id FROM profile WHERE user_email_name ='$user' ");
    $profileid = mysql_fetch_row($results);

    if ($profileid) {

        return $profileid[0];
    }

    return false;
} //end getid

// Function to get email by user ID
function getEmailById($user_id)
{
    global $pdo;
    try {
        $sql = "SELECT user_email FROM users WHERE user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['user_email'];
    } catch (PDOException $e) {
        return "Error: " . $e->getMessage();
    }
}

function highlight($subject, $pattern)
{

    $searcharray = explode(" ", $pattern);

    $highlighted = array();
    foreach ($searcharray as $s) {
        $highlighted[] = "<b>" . $s . "</b>";
    }
    $subject = str_ireplace($searcharray, $highlighted, $subject);

    return $subject;
}

/*****************************************************************************************************************
 * Hash a string using bcrypt with specified complexity
 *
 * @param  string $password input string
 * @param  integer $complexity bcrypt exponential cost
 * @return string
 */
function bcrypt_hash($password, $complexity = 12)
{
    if ($complexity < 4 || $complexity > 31) {
        throw new InvalidArgumentException("BCrypt complexity must be between 4 and 31");
    }

    // CRYPT_BLOWFISH salts must be 22 alphanumeric characters long
    $random = get_random_alnum_salt(22);

    // The crypt function decides which algorithm to use (we need Blowfish) based on
    // the format of the salt parameter
    $salt = sprintf('$2a$%02d$%s', $complexity, $random);

    return crypt($password, $salt);
}

/**
 * This generates a random alphanumeric string of length $length.
 *
 * This may not be a cryptographic grade random string generation
 * function - but it is good enough for our example
 *
 * @param  integer $length random string length
 * @return string
 */
function get_random_alnum_salt($length)
{
    static $chars = null;
    if (! $chars) {
        $chars = implode('', array_merge(range('a', 'z'), range('A', 'Z'), range(0, 9)));
    }

    $salt = '';
    for ($i = 0; $i < $length; $i++) {
        $salt .= $chars[mt_rand(0, 61)];
    }
    return $salt;
}

/**
 * Check a password against a hashed string
 *
 * @param  string $password cleartext password
 * @param  string $hashed bcrypt format hashed string
 * @return boolean
 */
function bcrypt_check_hash($password, $hashed)
{
    // Do some quick validation that $hashed is indeed a bcrypt hash
    if (strlen($hashed) != 60 || ! preg_match('/^\$2a\$\d{2}\$/', $hashed)) {
        throw new InvalidArgumentException("Provided hash is not a bcrypt string hash");
    }
    return (crypt($password, $hashed) === $hashed);
}




function set_session_vars()
{
    $nb_args = func_num_args();
    $arg_list = func_get_args();
    for ($i = 0; $i < $nb_args; $i++) {
        //global $$arg_list[$i];
        //global $$arg_list[$i];
        global ${$arg_list[$i]};

        $_SESSION[$arg_list[$i]] = $$arg_list[$i];
    }
}


// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup)
{
    // For security, start by assuming the visitor is NOT authorized. 
    $isValid = False;

    // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
    // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
    if (!empty($UserName)) {

        // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
        // Parse the strings into arrays. 
        $arrUsers = Explode(",",
            $strUsers
        );
        $arrGroups = Explode(",", $strGroups);
        if (in_array($UserName, $arrUsers)) {
            $isValid = true;
        }
        // Or, you may restrict access to only certain users based on their username. 
        if (in_array($UserGroup, $arrGroups)) {
            $isValid = true;
        }
        if (($strUsers == "") && true) {
            $isValid = true;
        }
    }
    return $isValid;
}
