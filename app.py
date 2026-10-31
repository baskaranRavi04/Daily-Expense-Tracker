import sqlite3
import pandas as pd
import streamlit as st
from datetime import date

# ---------------- PAGE CONFIGURATION ----------------
st.set_page_config(
    page_title="Daily Expense Tracker",
    page_icon="💰",
    layout="wide"
)

# ---------------- DATABASE CONNECTION ----------------
conn = sqlite3.connect("expenses.db", check_same_thread=False)
cursor = conn.cursor()

# Create expenses table
cursor.execute("""
CREATE TABLE IF NOT EXISTS expenses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    amount REAL NOT NULL,
    category TEXT NOT NULL,
    expense_date TEXT NOT NULL
)
""")
conn.commit()

# ---------------- PRELOADED SAMPLE DATASET ----------------
# Add sample expenses only when the database is empty.
cursor.execute("SELECT COUNT(*) FROM expenses")
count = cursor.fetchone()[0]

if count == 0:
    sample_data = [
        ("Breakfast", 120, "Food", "2026-10-01"),
        ("Bus Fare", 50, "Travel", "2026-10-02"),
        ("Shopping", 850, "Shopping", "2026-10-03"),
        ("Electricity Bill", 1200, "Bills", "2026-10-05"),
        ("Lunch", 180, "Food", "2026-10-06"),
        ("Movie Ticket", 300, "Entertainment", "2026-10-08"),
        ("Medicine", 250, "Health", "2026-10-10"),
        ("Stationery", 150, "Education", "2026-10-12"),
    ]

    cursor.executemany(
        """
        INSERT INTO expenses (title, amount, category, expense_date)
        VALUES (?, ?, ?, ?)
        """,
        sample_data
    )
    conn.commit()


# ---------------- HELPER FUNCTION ----------------
def get_expenses():
    return pd.read_sql_query(
        """
        SELECT id, title, amount, category, expense_date
        FROM expenses
        ORDER BY expense_date DESC, id DESC
        """,
        conn
    )


def format_rupees(amount):
    return f"₹{amount:,.2f}"


# ---------------- SIDEBAR NAVIGATION ----------------
st.title("💰 Daily Expense Tracker")
st.caption("Record, manage and understand your daily spending.")

menu = st.sidebar.selectbox(
    "Select Option",
    [
        "Dashboard",
        "Add Expense",
        "View Expenses",
        "Search Expense",
        "Edit Expense",
        "Delete Expense",
        "Expense Summary"
    ]
)

categories = [
    "Food",
    "Travel",
    "Shopping",
    "Bills",
    "Education",
    "Health",
    "Entertainment",
    "Other"
]


# ---------------- 1. DASHBOARD ----------------
if menu == "Dashboard":
    st.header("📊 Dashboard")

    df = get_expenses()

    total_expenses = float(df["amount"].sum()) if not df.empty else 0

    today_string = date.today().isoformat()
    today_expenses = (
        float(df[df["expense_date"] == today_string]["amount"].sum())
        if not df.empty else 0
    )

    current_month = date.today().strftime("%Y-%m")
    monthly_total = (
        float(df[df["expense_date"].str.startswith(current_month)]["amount"].sum())
        if not df.empty else 0
    )

    col1, col2, col3 = st.columns(3)

    col1.metric("Total Expenses", format_rupees(total_expenses))
    col2.metric("Today's Expenses", format_rupees(today_expenses))
    col3.metric("This Month", format_rupees(monthly_total))

    st.subheader("Recent Expenses")

    if not df.empty:
        st.dataframe(
            df[["expense_date", "title", "category", "amount"]],
            use_container_width=True,
            hide_index=True
        )
    else:
        st.info("No expenses available. Add an expense to get started.")

    if not df.empty:
        col1, col2 = st.columns(2)

        with col1:
            st.subheader("Category-wise Spending")
            category_data = df.groupby("category")["amount"].sum()
            st.bar_chart(category_data)

        with col2:
            st.subheader("Monthly Spending")
            df["month"] = df["expense_date"].str[:7]
            monthly_data = df.groupby("month")["amount"].sum().sort_index()
            st.line_chart(monthly_data)


# ---------------- 2. ADD EXPENSE ----------------
elif menu == "Add Expense":
    st.header("➕ Add Daily Expense")

    with st.form("add_expense_form", clear_on_submit=True):
        title = st.text_input("Expense Name")

        col1, col2 = st.columns(2)

        amount = col1.number_input(
            "Amount (₹)",
            min_value=0.0,
            step=10.0,
            format="%.2f"
        )

        category = col2.selectbox("Category", categories)

        expense_date = st.date_input(
            "Expense Date",
            value=date.today()
        )

        submitted = st.form_submit_button("Add Expense", type="primary")

        if submitted:
            if title.strip() and amount > 0:
                cursor.execute(
                    """
                    INSERT INTO expenses (title, amount, category, expense_date)
                    VALUES (?, ?, ?, ?)
                    """,
                    (
                        title.strip(),
                        amount,
                        category,
                        expense_date.isoformat()
                    )
                )
                conn.commit()
                st.success("Expense added successfully!")
            else:
                st.error("Please enter a valid expense name and amount.")


# ---------------- 3. VIEW EXPENSES ----------------
elif menu == "View Expenses":
    st.header("📋 All Expenses")

    df = get_expenses()

    if not df.empty:
        st.dataframe(
            df,
            use_container_width=True,
            hide_index=True
        )

        st.metric(
            "Total Expenses",
            format_rupees(float(df["amount"].sum()))
        )
    else:
        st.info("No expenses found.")


# ---------------- 4. SEARCH EXPENSE ----------------
elif menu == "Search Expense":
    st.header("🔍 Search Expense")

    df = get_expenses()

    search_text = st.text_input(
        "Search by expense name or category",
        placeholder="Example: Food, Travel, Breakfast"
    )

    if not df.empty:
        if search_text.strip():
            filtered_df = df[
                df["title"].str.contains(search_text, case=False, na=False)
                | df["category"].str.contains(search_text, case=False, na=False)
            ]
        else:
            filtered_df = df

        st.write(f"Expenses found: {len(filtered_df)}")

        st.dataframe(
            filtered_df,
            use_container_width=True,
            hide_index=True
        )

        st.metric(
            "Search Result Total",
            format_rupees(float(filtered_df["amount"].sum()))
        )
    else:
        st.info("No expenses available to search.")


# ---------------- 5. EDIT EXPENSE ----------------
elif menu == "Edit Expense":
    st.header("✏️ Edit Expense")

    df = get_expenses()

    if not df.empty:
        expense_options = {
            f'ID {int(row.id)} - {row.title} - {row.expense_date}': int(row.id)
            for row in df.itertuples()
        }

        selected_label = st.selectbox(
            "Select Expense",
            list(expense_options.keys())
        )

        selected_id = expense_options[selected_label]
        selected = df[df["id"] == selected_id].iloc[0]

        with st.form("edit_expense_form"):
            new_title = st.text_input(
                "Expense Name",
                value=str(selected["title"])
            )

            col1, col2 = st.columns(2)

            new_amount = col1.number_input(
                "Amount (₹)",
                min_value=0.0,
                value=float(selected["amount"]),
                step=10.0,
                format="%.2f"
            )

            current_category = str(selected["category"])
            category_index = (
                categories.index(current_category)
                if current_category in categories else 0
            )

            new_category = col2.selectbox(
                "Category",
                categories,
                index=category_index
            )

            new_date = st.date_input(
                "Expense Date",
                value=date.fromisoformat(str(selected["expense_date"]))
            )

            update = st.form_submit_button(
                "Update Expense",
                type="primary"
            )

            if update:
                if new_title.strip() and new_amount > 0:
                    cursor.execute(
                        """
                        UPDATE expenses
                        SET title = ?, amount = ?, category = ?, expense_date = ?
                        WHERE id = ?
                        """,
                        (
                            new_title.strip(),
                            new_amount,
                            new_category,
                            new_date.isoformat(),
                            selected_id
                        )
                    )
                    conn.commit()
                    st.success("Expense updated successfully!")
                    st.rerun()
                else:
                    st.error("Please enter a valid expense name and amount.")
    else:
        st.info("No expenses available to edit.")


# ---------------- 6. DELETE EXPENSE ----------------
elif menu == "Delete Expense":
    st.header("🗑️ Delete Expense")

    df = get_expenses()

    if not df.empty:
        expense_options = {
            f'ID {int(row.id)} - {row.title} - {row.expense_date}': int(row.id)
            for row in df.itertuples()
        }

        selected_label = st.selectbox(
            "Select Expense to Delete",
            list(expense_options.keys())
        )

        selected_id = expense_options[selected_label]

        confirm_delete = st.checkbox(
            "I confirm that I want to delete this expense."
        )

        if st.button(
            "Delete Expense",
            type="primary",
            disabled=not confirm_delete
        ):
            cursor.execute(
                "DELETE FROM expenses WHERE id = ?",
                (selected_id,)
            )
            conn.commit()
            st.success("Expense deleted successfully!")
            st.rerun()
    else:
        st.info("No expenses available to delete.")


# ---------------- 7. EXPENSE SUMMARY ----------------
elif menu == "Expense Summary":
    st.header("📈 Expense Summary")

    df = get_expenses()

    if not df.empty:
        total = float(df["amount"].sum())

        st.metric("Total Expenses", format_rupees(total))

        # Monthly Summary
        st.subheader("Monthly Expense Summary")

        df["month"] = df["expense_date"].str[:7]

        monthly_summary = (
            df.groupby("month", as_index=False)["amount"]
            .sum()
            .sort_values("month")
        )

        st.bar_chart(
            monthly_summary.set_index("month")["amount"]
        )

        st.dataframe(
            monthly_summary.rename(
                columns={
                    "month": "Month",
                    "amount": "Total Expenses (₹)"
                }
            ),
            use_container_width=True,
            hide_index=True
        )

        # Category Summary
        st.subheader("Category-wise Expense Summary")

        category_summary = (
            df.groupby("category", as_index=False)["amount"]
            .sum()
            .sort_values("amount", ascending=False)
        )

        st.bar_chart(
            category_summary.set_index("category")["amount"]
        )

        st.dataframe(
            category_summary.rename(
                columns={
                    "category": "Category",
                    "amount": "Total Expenses (₹)"
                }
            ),
            use_container_width=True,
            hide_index=True
        )
    else:
        st.info("Add expenses to view summaries.")


# ---------------- FOOTER ----------------
st.sidebar.markdown("---")
st.sidebar.caption("Daily Expense Tracker")
st.sidebar.caption("Python | Streamlit | SQLite")
