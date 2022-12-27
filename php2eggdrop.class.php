<?php
class EggdropController
{

    // Connection variables
    protected $host;
    protected $port;
    protected $username;
    protected $password;

    // Command variables
    protected $command;

    // Connection resource
    protected $conn;

    /**
     * Constructs a new EggdropController object
     *
     * @param string $host The hostname of the eggdrop
     * @param int $port The port number of the eggdrop
     * @param string $username The username to use when connecting to the eggdrop
     * @param string $password The password to use when connecting to the eggdrop
     * @param string $command The command to send to the eggdrop
     */
    public function __construct($host, $port, $username, $password, $command = "")
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->command = $command;
    }

    /**
     * Connects to the eggdrop and logs in
     *
     * @return resource|bool The connection resource if the connection was successful, false otherwise
     */
    public function connect()
    {
        try {
            // Connect to the eggdrop
            $conn = fsockopen($this->host, $this->port, $errno, $errstr, 15);
            if (!$conn) {
                throw new Exception("Error connecting to eggdrop: $errstr ($errno)");
            }

            // Read the server's response
            $response = fgets($conn);

            // Wait for the server to ask for the username
            while (!preg_match("/Please enter your handle/", $response)) {
                $response = fgets($conn);
            }

            // Send the username
            fputs($conn, "$this->username\n");

            // Read the server's response
            $response = fgets($conn);

            // Wait for the server to ask for the password
            while (!preg_match("/Enter your password/", $response)) {
                $response = fgets($conn);
            }

            // Send the password
            fputs($conn, "$this->password\n");

            // Read the server's response
            $response = fgets($conn);

            // Wait for the server to indicate that we have joined the party line
            while (!preg_match("/joined the party line/", $response)) {
                $response = fgets($conn);
            }

            // Save the connection resource
            $this->conn = $conn;

            return $conn;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Makes sure that we're connected to the eggdrop and logs in if necessary
     *
     * @return bool True if the connection is open, false otherwise
     */
    protected function ensureConnection()
    {
        // If we're already connected, return true
        if ($this->conn) {
            return true;
        }

        // Try to connect to the eggdrop and log in
        return $this->connect();
    }

    /**
     * Sends a command to the eggdrop
     *
     * @param string $command The command to send (e.g. ".+chan")
     * @param string $arguments The arguments for the command (e.g. "#channel")
     * @return bool|string The response from the eggdrop, or false on failure
     */
    public function sendCommand($command, $arguments = "")
    {
        try {
            // Make sure we're connected to the eggdrop
            if (!$this->ensureConnection()) {
                throw new Exception("Error connecting to eggdrop");
            }

            // Send the command to the eggdrop
            fputs($this->conn, "$command $arguments\n");

            // Read the server's response
            $response = fgets($this->conn);

            // Return the response
            return $response;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Changes the nickname of a user in the party-line
     *
     * @param string $oldNick The current nickname of the user
     * @param string $newNick The new nickname of the user
     * @return bool True if the nickname was successfully changed, false otherwise
     */
    public function changeNick($oldNick, $newNick)
    {
        return $this->sendCommand(".chnick", "$oldNick $newNick");
    }

    /**
     * Creates a new user record for the handle given
     *
     * @param string $handle The handle for the new user
     * @param string $hostmask The hostmask for the new user
     * @return bool True if the user was successfully created, false otherwise
     */
    public function addUser($handle, $hostmask = "")
    {
        return $this->sendCommand(".+user", "$handle $hostmask");
    }

    /**
     * Removes the specified handle's user record
     *
     * @param string $handle The handle of the user to remove
     * @return bool True if the user was successfully removed, false otherwise
     */
    public function removeUser($handle)
    {
        return $this->sendCommand(".-user", $handle);
    }

    /**
     * Adds a channel to the bot's channel list
     *
     * @param string $channel The channel to add
     * @param string $options The options for the channel
     * @return bool True if the channel was successfully added, false otherwise
     */
    public function addChannel($channel, $options = "")
    {
        return $this->sendCommand(".+chan", "$channel $options");
    }

    /**
     * Removes all information about a channel from the bot
     *
     * @param string $channel The channel to remove
     * @return bool True if the channel was successfully removed, false otherwise
     */
    public function removeChannel($channel)
    {
        return $this->sendCommand(".-chan", $channel);
    }

    /**
     * Changes the channel settings for one specific channel or all channels
     *
     * @param string $channel The channel to change the settings for
     * @param string $settings The new settings for the channel
     * @return bool True if the channel settings were successfully changed, false otherwise
     */
    public function setChannelSettings($channel, $settings)
    {
        return $this->sendCommand(".chanset", "$channel $settings");
    }

    /**
     * Lists all the settings for the bot on the given channel
     *
     * @param string $channel The channel to get the settings for
     * @return bool|string The settings for the channel, or false on failure
     */
    public function getChannelInfo($channel)
    {
        return $this->sendCommand(".chaninfo", $channel);
    }

    /**
     * Changes the channel settings for one specific channel
     *
     * @param string $channel The channel to change the settings for
     * @param string $setting The setting to change
     * @param string $value The new value for the setting
     * @return bool True if the channel settings were successfully changed, false otherwise
     */
    public function setChannelSetting($channel, $setting, $value)
    {
        return $this->sendCommand(".chanset", "$channel $setting $value");
    }

    /**
     * Changes the channel mode for the specified channel
     *
     * @param string $channel The channel to change the mode for
     * @param string $mode The mode to set for the channel (e.g. "+mnt")
     * @return bool True if the channel mode was successfully changed, false otherwise
     */
    public function setChannelMode($channel, $mode)
    {
        return $this->setChannelSetting($channel, "chanmode", $mode);
    }

    /**
     * Sets the idle kick time for the specified channel
     *
     * @param string $channel The channel to set the idle kick time for
     * @param int $minutes The number of minutes of idle time before a user is kicked
     * @return bool True if the idle kick time was successfully set, false otherwise
     */
    public function setIdleKickTime($channel, $minutes)
    {
        return $this->setChannelSetting($channel, "idle_kick", $minutes);
    }
    /**
     * Sets the maximum number of users allowed in the specified channel
     *
     * @param string $channel The channel to set the maximum user count for
     * @param int $maxUsers The maximum number of users allowed in the channel
     * @return bool True if the maximum user count was successfully set, false otherwise
     */
    public function setMaxUsers($channel, $maxUsers)
    {
        return $this->setChannelSetting($channel, "max_users", $maxUsers);
    }

    /**
     * Sets the topic for the specified channel
     *
     * @param string $channel The channel to set the topic for
     * @param string $topic The new topic for the channel
     * @return bool True if the topic was successfully set, false otherwise
     */
    public function setTopic($channel, $topic)
    {
        return $this->sendCommand(".topic", "$channel $topic");
    }

    /**
     * Gets the topic for the specified channel
     *
     * @param string $channel The channel to get the topic for
     * @return bool|string The topic for the channel, or false on failure
     */
    public function getTopic($channel)
    {
        return $this->sendCommand(".topic", $channel);
    }

    /**
     * Changes the password for the specified user
     *
     * @param string $handle The handle of the user to change the password for
     * @param string $password The new password for the user
     * @return bool True if the password was successfully changed, false otherwise
     */
    public function setPassword($handle, $password)
    {
        return $this->sendCommand(".chpass", "$handle $password");
    }

    /**
     * Changes the flags for the specified user
     *
     * @param string $handle The handle of the user to change the flags for
     * @param string $flags The new flags for the user
     * @return bool True if the flags were successfully changed, false otherwise
     */
    public function setFlags($handle, $flags)
    {
        return $this->sendCommand(".chflags", "$handle $flags");
    }
    /**
     * Gets the flags for the specified user
     *
     * @param string $handle The handle of the user to get the flags for
     * @return bool|string The flags for the user, or false on failure
     */
    public function getFlags($handle)
    {
        return $this->sendCommand(".userinfo", $handle);
    }

    /**
     * Bans the specified hostmask from the specified channel
     *
     * @param string $channel The channel to ban the hostmask from
     * @param string $hostmask The hostmask to ban
     * @return bool True if the ban was successfully added, false otherwise
     */
    public function ban($channel, $hostmask)
    {
        return $this->sendCommand(".+ban", "$channel $hostmask");
    }

    /**
     * Unbans the specified hostmask from the specified channel
     *
     * @param string $channel The channel to unban the hostmask from
     * @param string $hostmask The hostmask to unban
     * @return bool True if the ban was successfully removed, false otherwise
     */
    public function unban($channel, $hostmask)
    {
        return $this->sendCommand(".-ban", "$channel $hostmask");
    }

    /**
     * Kicks a user from the specified channel
     *
     * @param string $channel The channel to kick the user from
     * @param string $handle The handle of the user to kick
     * @param string $reason The reason for the kick
     * @return bool True if the user was successfully kicked, false otherwise
     */
    public function kick($channel, $handle, $reason = "")
    {
        return $this->sendCommand(".kick", "$channel $handle $reason");
    }

    /**
     * Bans and then kicks a
     * /
    /**
     * Invites a user to the specified channel
     *
     * @param string $channel The channel to invite the user to
     * @param string $handle The handle of the user to invite
     * @return bool True if the invitation was successfully sent, false otherwise
     */
    public function invite($channel, $handle)
    {
        return $this->sendCommand(".invite", "$channel $handle");
    }

    /**
     * Makes the bot join the specified channel
     *
     * @param string $channel The channel to join
     * @param string $key The key for the channel (if necessary)
     * @return bool True if the bot was successfully joined to the channel, false otherwise
     */
    public function joinChannel($channel, $key = "")
    {
        return $this->sendCommand(".join", "$channel $key");
    }

    /**
     * Makes the bot part the specified channel
     *
     * @param string $channel The channel to part
     * @param string $reason The reason for parting the channel
     * @return bool True if the bot was successfully parted from the channel, false otherwise
     */
    public function partChannel($channel, $reason = "")
    {
        return $this->sendCommand(".part", "$channel $reason");
    }

    /**
     * Makes the bot op the specified user in the specified channel
     *
     * @param string $channel The channel to op the user in
     * @param string $handle The handle of the user to op
     * @return bool True if the user was successfully oped, false otherwise
     */
    public function op($channel, $handle)
    {
        return $this->sendCommand(".op", "$channel $handle");
    }

    /**
     * Makes the bot voice the specified user in the specified channel
     *
     * @param string $channel The channel to voice the user in
     * @param string $handle The handle of the user to voice
     * @return bool True if the user was successfully voiced, false otherwise
     */
    public function voice($channel, $handle)
    {
        return $this->sendCommand(".voice", "$channel $handle");
    }

    /**
     * Makes the bot devoice the specified user in the specified channel
     *
     * @param string $channel The channel to devoice the user in
     * @param string $handle The handle of the user to devoice
     * @return bool True if the user was successfully devoiced, false otherwise
     */
    public function devoice($channel, $handle)
    {
        return $this->sendCommand(".devoice", "$channel $handle");
    }

    /**
     * Kicks the specified user from the specified channel
     *
     * @param string $channel The channel to kick the user from
     * @param string $handle The handle of the user to kick
     * @param string $reason The reason for kicking the user
     * @return bool True if the user was successfully kicked, false otherwise
     */
    public function kick($channel, $handle, $reason = "")
    {
        return $this->sendCommand(".kick", "$channel $handle $reason");
    }

    /**
     * Bans the specified user from the specified channel
     *
     * @param string $channel The channel to ban the user from
     * @param string $handle The handle of the user to ban
     * @param string $reason The reason for banning the user
     * @return bool True if the user was successfully banned, false otherwise
     */
    public function ban($channel, $handle, $reason = "")
    {
        return $this->sendCommand(".ban", "$channel $handle $reason");
    }

    /**
     * Unbans the specified user from the specified channel
     *
     * @param string $channel The channel to unban the user from
     * @param string $handle The handle of the user to unban
     * @return bool True if the user was successfully unbanned, false otherwise
     */
    public function unban($channel, $handle)
    {
        return $this->sendCommand(".unban", "$channel $handle");
    }

    /**
     * Sets the topic for the specified channel
     *
     * @param string $channel The channel to set the topic for
     * @param string $topic The new topic for the channel
     * @return bool True if the topic was successfully set, false otherwise
     */
    public function setTopic($channel, $topic)
    {
        return $this->sendCommand(".topic", "$channel $topic");
    }

    /**
     * Gets the current topic for the specified channel
     *
     * @param string $channel The channel to get the topic for
     * @return bool|string The current topic for the channel, or false on failure
     */
    public function getTopic($channel)
    {
        return $this->sendCommand(".topic", $channel);
    }

    /**
     * Lists all the users in the specified channel
     *
     * @param string $channel The channel to list the users for
     * @return bool|string The list of users, or false on failure
     */
    public function listUsers($channel)
    {
        return $this->sendCommand(".users", $channel);
    }

    /**
     * Gets the hostmask of the specified user
     *
     * @param string $handle The handle of the user to get the hostmask for
     * @return bool|string The hostmask of the user, or false on failure
     */
    public function getHostmask($handle)
    {
        return $this->sendCommand(".hostmask", $handle);
    }

    /**
     * Changes the password of the specified user
     *
     * @param string $handle The handle of the user to change the password for
     * @param string $password The new password for the user
     * @return bool True if the password was successfully changed, false otherwise
     */
    public function changePassword($handle, $password)
    {
        return $this->sendCommand(".passwd", "$handle $password");
    }

    /**
     * Displays the eggdrop version and build information
     *
     * @return bool|string The version and build information, or false on failure
     */
    public function getVersion()
    {
        return $this->sendCommand(".version");
    }

    /**
     * Displays the current time
     *
     * @return bool|string The current time, or false on failure
     */
    public function getTime()
    {
        return $this->sendCommand(".time");
    }

    /**
     * Displays the current load average
     *
     * @return bool|string The current load average, or false on failure
     */
    public function getLoadAverage()
    {
        return $this->sendCommand(".load");
    }

    /**
     * Displays the current uptime of the eggdrop
     *
     * @return bool|string The current uptime of the eggdrop, or false on failure
     */
    public function getUptime()
    {
        return $this->sendCommand(".uptime");
    }

    /**
     * Displays the current memory usage of the eggdrop
     *
     * @return bool|string The current memory usage of the eggdrop, or false on failure
     */
    public function getMemoryUsage()
    {
        return $this->sendCommand(".memory");
    }
}