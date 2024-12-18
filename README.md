# CoCart API - Core

<p align="center">
	<a href="https://github.com/co-cart/co-cart/blob/trunk/license.txt" target="_blank">
		<img src="https://img.shields.io/badge/license-GPL--3.0%2B-red.svg" alt="Licence">
	</a>
	<a href="https://wordpress.org/plugins/cart-rest-api-for-woocommerce/">
		<img src="https://poser.pugx.org/co-cart/co-cart/v/stable" alt="Latest Stable Version">
	</a>
	<a href="https://wordpress.org/plugins/cart-rest-api-for-woocommerce/">
		<img src="https://img.shields.io/wordpress/plugin/dt/cart-rest-api-for-woocommerce.svg" alt="WordPress Plugin Downloads">
	</a>
	<a href="https://polar.sh/cocartheadless/issues">
		<img src="https://polar.sh/embed/seeks-funding-shield.svg?org=cocartheadless" />
	</a>
</p>

Welcome to the CoCart core repository on GitHub. Here you can browse the source code, [look at open issues](https://github.com/co-cart/co-cart/issues?q=is%3Aopen+is%3Aissue) and keep track of development. We recommend all developers to follow the [CoCart development blog](https://cocart.dev/news/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=cocartcore) to stay up to date about everything happening in the project. You can also [follow @cocartapi](https://twitter.com/cocartapi) on Twitter for the latest development updates.

If you are looking for documentation, head over here: [https://docs.cocart.xyz](https://docs.cocart.xyz/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=cocartcore)

👋 Looking to [contribute to CoCart](https://github.com/co-cart/co-cart/blob/development/.github/CONTRIBUTING.md)?

🫶 Show some appreciation by [sponsoring](https://github.com/co-cart/co-cart/blob/development/BACKERS.md).

### Description

Transform your WooCommerce store into a lightning-fast, decoupled shopping experience with CoCart — an open-source REST API designed to set your store free from WordPress's limitations. Build in modern frameworks like Astro, React, Vue, or Next.js, putting full control back in your hands and making complex customizations a breeze.

Built by developers, for developers, [CoCart](https://cocartapi.com/?utm_medium=repo&utm_source=github.com&utm_campaign=readme&utm_content=cocartcore) is more than just an API extension. It’s a suite of tools that make managing a headless WooCommerce store seamless and fast—without the constraints of a traditional WordPress theme. Allowing you to focus on building a unique and seamless shopping experience.

[Want to try it out?](https://cocartapi.com/try-free-demo/?utm_medium=repo&utm_source=github.com&utm_campaign=readme&utm_content=cocartcore)

#### What is WooCommerce?

WooCommerce is a free open-source eCommerce plugin that provides a **flexible, and efficient** way to build cutting-edge stores with WordPress at it's core.

#### Why do I need CoCart?

If you are looking to create a great store for your customers that is fast and unrestricted, decoupling your store is a no-brainer. However, accessing the front side via the REST API is not designed in WooCommerce, that's where CoCart comes in. It handles the roadblocks and eliminates the hassle of creating your own REST API endpoints and provides all the essential features for a powerful, headless eCommerce experience.

#### Features

* **Source of Truth** - CoCart integrates with WooCommerce's Data Stores API and includes WooCommerce hooks, making it compatible with most WooCommerce extensions right out of the box.
* **Session Control Without Cookies** - CoCart uses a secure session key system, reducing server load and avoiding cookie issues for a smoother experience.
* **Easy Authentication** - No need for Admin API Keys — customers can log in multiple ways with options like username+password, email+password or phone number+password.
* **Flexible Domain Control** - CORS problems? No worries. CoCart lets you control origin access to prevent cross-domain issues.
* **Efficient Product Search** - Search by product name, ID or SKU, filter products quickly without compromising privacy.
* **Real-Time Cart Validation** - Enjoy up-to-date cart accuracy with automatic stock checks, total calculations, and fee adjustments.
* **No Headless Checkout?** - Easily load any cart session into the native WooCommerce checkout (if you prefer) and auto-login customers without further user interaction.
* **Customizable Callbacks** - Register your own cart callbacks without creating new endpoints. CoCart handles the rest, returning responses once complete.
* **Cart Session Tracking** - Monitor all cart sessions, including expired or expiring carts, to better track customer engagement.
* **Name Your Price** - Offer customers the option to pay what they want, encourage your audience and increasing conversion rates.
* **Bulk Requests** - Batch multiple cart requests at once for efficiency and faster responses.

And this is just the start. With CoCart, your WooCommerce store can be faster, more flexible, and ready to take on any custom solution you dream up.

### 📦 Serious about going headless?

Transform your WooCommerce store into a lightning-fast, decoupled shopping experience with CoCart and unlock your store’s true potential. Upgrade to access premium features and extend CoCart’s capabilities to take your headless WooCommerce store even further.

[See what else we have in store](https://cocartapi.com/pricing/?utm_medium=repo&utm_source=github.com&utm_campaign=readme&utm_content=cocartcore).

## 👍 Add-ons to further enhance CoCart

We also have add-ons that extend CoCart to enhance your development and your customers shopping experience.

* **[CoCart - CORS](https://github.com/cocart-headless/cocart-cors)** enables support for CORS to allow CoCart to work across multiple domains.
* **[CoCart - Rate Limiting](https://github.com/cocart-headless/cocart-rate-limiting)** enables the rate limiting feature for CoCart Plus or higher.
* **[CoCart - JWT Authentication](https://github.com/cocart-headless/cocart-jwt-authentication)** allows you to authenticate via a simple JWT Token.
* **[CoCart - Cart Enhanced](https://github.com/cocart-headless/cocart-get-cart-enhanced)** enhances the data returned for the cart and the items added to it.
* and more add-ons in development.

They work with the core of CoCart already, and these add-ons of course come with support too.

## 🧰 Developer Tools

* **[CoCart Beta Tester](https://github.com/co-cart/cocart-beta-tester)** allows you to test with bleeding edge versions of CoCart from the GitHub repo.
* **[CoCart VSCode](https://github.com/co-cart/cocart-vscode)** extension for Visual Studio Code adds snippets and auto-completion of functions, classes and hooks.
* **[CoCart Carts in Session](https://github.com/co-cart/cocart-carts-in-session)** allows you to view all the carts in session via the WordPress admin.
* **[CoCart Product Support Boilerplate](https://github.com/co-cart/cocart-product-support-boilerplate)** provides a basic boilerplate for supporting a different product type to add to the cart with validation including adding your own parameters.
* **[CoCart Cart Callback Example](https://github.com/co-cart/cocart-cart-callback-example)** provides you an example of registering a callback that can be triggered when updating the cart.

## Need Support?

We aim to provide regular support for the CoCart plugin via [our Discord community server](https://cocartapi.com/community/?utm_medium=repo&utm_source=github.com&utm_campaign=readme&utm_content=cocartcore). Please understand that we do prioritize support for our [paying customers](https://cocartapi.com/pricing/?utm_medium=repo&utm_source=github.com&utm_campaign=readme&utm_content=cocartcore).

| Core | Plus |
|------|------|
| <a href="https://buy.polar.sh/polar_cl_jMOIZK9WxNTNmP_VZ6DsC-gTCM1sq38KDYw3MAb3PvM"><img src="https://polar.sh/embed/product.svg?organizationId=d2729179-cce9-479c-96cc-43de492209cc&productId=e954b15c-152a-4dbf-9727-98aadf449a99" alt="CoCart Core 'Early Access'" /></a> | <a href="https://buy.polar.sh/polar_cl_FZ4d7RgIRViEhkyfi5Q0A84N0jirhgoPKbWJKjPSKTA"><img src="https://polar.sh/embed/product.svg?organizationId=d2729179-cce9-479c-96cc-43de492209cc&productId=dada6fe2-0744-4393-a50d-a42c563b4825" alt="CoCart Plus - Single Site" /></a> |

## 🐞 Bug Reporting

Bug reports for CoCart are welcomed in the [CoCart repository on GitHub](https://github.com/co-cart/co-cart/issues/new). Please note that GitHub is not a support forum, and that issues that aren’t properly qualified as bugs will be closed.

## Support CoCart

Please consider starring ✨ and sharing 👍 the project repo! This helps the project get known and grow with the community. If you like to help further CoCart's development, why not [consider sponsoring](https://github.com/co-cart/co-cart/blob/development/BACKERS.md).

Thank you! 🙏

## Testimonials

> An excellent plugin, which makes building a headless WooCommerce experience a breeze. Easy to use, nearly zero setup time.
>
> Harald Schneider ⭐️⭐️⭐️⭐️⭐️

> What can I say this thing has it all. It is the “Missing WooCommerce REST API plugin” without it I was managing users cart myself in weird and wonderful but hacky ways. NOT GOOD and so vulnerable. Then I stumbled upon CoCart and with the help of Seb I got it working how I needed it and he has been supporting me with even the smallest of queries. Really appreciate your work and continued support Seb.
>
> **[Joel Pierre](https://github.com/joelpierre)** – JPPdesigns Web design & Development ⭐️⭐️⭐️⭐️⭐️

> This plugin was critical to achieve my project of building a headless / decoupled WooCommerce store. I wanted to provide my clients with a CMS to manage their store, but wanted to build the front-end in React. I was able to fetch content over the WooCommerce REST API, but otherwise would not have been able to fetch the cart, and add & remove items if not for this plugin.
>
> Thank you very much Sébastien for sharing this extension, you’ve saved me a lot of time.
>
> **Allan Pooley** – Little and Big ⭐️⭐️⭐️⭐️⭐️

> Thanks for doing such a great work with this! Works exactly as expected and CoCart seems to have a nice community around it. The founder seems really devoted and that’s one of the key things for a plugin like this to live on and get the right updates in the future. We just got ourselves the lifetime subscription.
>
> **MightyGroup** – Rikard Kling ⭐️⭐️⭐️⭐️⭐️

[See the wall of love](https://cocartapi.com/wall-of-love/?utm_medium=repo&utm_source=github.com&utm_campaign=readme&utm_content=cocartcore) for more testimonials. We’d also love to hear what you have to say. [Share your thoughts](https://testimonial.to/cocart) and help others discover if CoCart is for them.

---

## CoCart Channels

We have different channels at your disposal where you can find information about the CoCart project, discuss it and get involved:

[![Twitter: cocartapi](https://img.shields.io/twitter/follow/cocartapi?style=social)](https://twitter.com/cocartapi) [![CoCart GitHub Stars](https://img.shields.io/github/stars/co-cart/co-cart?style=social)](https://github.com/co-cart/co-cart)

<ul>
  <li>📖 <strong>Docs</strong>: this is the place to learn how to use CoCart API. <a href="https://docs.cocart.xyz/#getting-started">Get started!</a></li>
  <li>🧰 <strong>Resources</strong>: this is the hub of all CoCart resources to help you build a headless store. <a href="https://cocart.dev/?utm_medium=repo&utm_source=github.com&utm_campaign=readme&utm_content=cocartcore">Get resources!</a></li>
  <li>👪 <strong>Community</strong>: use our Discord chat room to share any doubts, feedback and meet great people. This is your place too to share <a href="https://cocartapi.com/community/?utm_medium=repo&utm_source=github.com&utm_campaign=readme&utm_content=cocartcore">how are you planning to use CoCart!</a></li>
  <li>🐞 <strong>GitHub</strong>: we use GitHub for bugs and pull requests, doubts are solved with the community.</li>
  <li>🐦 <strong>Social media</strong>: a more informal place to interact with CoCart users, reach out to us on <a href="https://twitter.com/cocartapi">Twitter.</a></li>
  <li>💌 <strong>Newsletter</strong>: do you want to receive the latest plugin updates and news? Subscribe <a href="https://twitter.com/cocartapi">here.</a></li>
</ul>

---

## Get involved

Do you like the idea of creating a headless store with WooCommerce? Got questions or feedback? We'd love to hear from you. Come [join our community](https://cocartapi.com/community/?utm_medium=repo&utm_source=github.com&utm_campaign=readme&utm_content=cocartcore)! ❤️

CoCart also welcomes contributions. There are many ways to support the project! If you don't know where to start, this guide might help >> [How to contribute?](https://github.com/co-cart/co-cart/blob/development/.github/CONTRIBUTING.md)

---

## Credits

Website [cocartapi.com](https://cocartapi.com?utm_medium=repo&utm_source=github.com&utm_campaign=readme&utm_content=cocartcore) &nbsp;&middot;&nbsp;
GitHub [@co-cart](https://github.com/co-cart) &nbsp;&middot;&nbsp;
Twitter [@cocartapi](https://twitter.com/cocartapi)

---

CoCart is developed and maintained by [Sébastien Dumont](https://github.com/seb86).
Founder of [CoCart Headless, LLC](https://github.com/cocart-headless).

Website [sebastiendumont.com](https://sebastiendumont.com) &nbsp;&middot;&nbsp;
GitHub [@seb86](https://github.com/seb86) &nbsp;&middot;&nbsp;
Twitter [@sebd86](https://twitter.com/sebd86)
