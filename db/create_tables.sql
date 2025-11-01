CREATE TABLE problems (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    type TEXT,
    input_value TEXT,
    input_base INTEGER,
    bit_width INTEGER,
    op TEXT,
    second_value TEXT,
    target_base INTEGER,
    result_value TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);
