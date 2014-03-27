# Language
```
language:changed (string $session, string $language) @deprecated
```

# History
```
history:change (string $branch, Fluid\History\History $history) @deprecated
```

# Map
```
map:change (string $branch, Fluid\Map\Map $map) @deprecated
```

# Data
```
data:get (string $session, string $language, string $page) @deprecated
```

# Websocket
```
websocket:connection:open (Ratchet\ConnectionInterface $conn)
websocket:connection:close (Ratchet\ConnectionInterface $conn)
```

# Website
```
website:page:change (string $sessionToken, string $page)
website:language:change (string $language)
```