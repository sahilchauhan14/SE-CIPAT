from playwright.sync_api import sync_playwright

def test_website():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=False)
        page = browser.new_page()

        # Open site
        page.goto("http://127.0.0.1:5500/index.html")
        page.wait_for_timeout(1000)

        print("Homepage loaded")

        # ---------------- LOGIN TEST ----------------
        page.click('a[data-page="login"]')
        page.wait_for_timeout(1000)

        page.fill("#login-email", "sahilchauhan7845@gmail.com")
        page.fill("#login-password", "sahil@1418")

        page.screenshot(path="before_login.png")

        page.click('button:has-text("Login")')
        page.wait_for_timeout(1000)

        page.screenshot(path="after_login.png")

        print("Login test done")

        # ---------------- REGISTER TEST ----------------
        page.click('a[data-page="register"]')
        page.wait_for_timeout(1000)

        page.fill("#register-name", "Sahil")
        page.fill("#register-email", "sahilchauhan7845@gmail.com")
        page.fill("#register-username", "sahi")
        page.fill("#register-password", "sahil@1418")
        page.fill("#register-confirm", "sahil@1418")

        page.check("#terms")

        page.screenshot(path="before_register.png")

        page.click('button:has-text("Create Account")')
        page.wait_for_timeout(1000)

        page.screenshot(path="after_register.png")

        print("Register test done")

        # ---------------- CONTACT TEST ----------------
        page.click('a[data-page="contact"]')
        page.wait_for_timeout(1000)

        page.fill("#name", "Sahil")
        page.fill("#email", "sahilchauhan7845@gmail.com")
        page.fill("#subject", "Test")
        page.fill("#message", "Hello")

        page.screenshot(path="before_contact.png")

        page.click('button:has-text("Send Message")')
        page.wait_for_timeout(1000)

        page.screenshot(path="after_contact.png")

        print("Contact test done")

        browser.close()


if __name__ == "__main__":
    test_website()


