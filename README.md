# Ninja API Explorer

A powerful WordPress plugin that provides a Swagger-like interface for exploring and testing WordPress REST APIs. Built with clean MVC architecture and modern design principles.

## 🚀 Features

### Core Functionality
- **Dynamic Route Discovery**: Automatically discovers all registered WordPress REST API routes
- **Interactive API Testing**: Built-in endpoint tester with support for all HTTP methods
- **Route Documentation**: Detailed information about each endpoint including parameters and examples
- **Real-time Filtering**: Filter routes by namespace, method, and public/private status
- **Smart Search**: Search through route paths and descriptions
- **Copy to Clipboard**: Easy URL copying for external testing

### User Interface
- **Clean Light Mode Design**: Modern, professional interface
- **Tabbed Navigation**: Organized view with All Routes, Grouped Routes, and API Tester tabs
- **Responsive Design**: Works perfectly on desktop, tablet, and mobile devices
- **WordPress Admin Integration**: Seamlessly integrated into WordPress admin panel
- **Collapsible Namespaces**: Organized route grouping with expand/collapse functionality

### Developer Features
- **MVC Architecture**: Clean separation of concerns with Models, Views, and Controllers
- **OpenAPI Specification**: Generates OpenAPI 3.0 specification for API documentation
- **Request Logging**: Logs API test requests for debugging and analysis
- **Extensible Design**: Easy to extend with custom functionality
- **Performance Optimized**: Efficient code with minimal resource usage

## 📋 Requirements

- **WordPress**: 5.0 or higher
- **PHP**: 7.4 or higher
- **Browser**: Modern browser with JavaScript enabled
- **Permissions**: Administrator access to WordPress admin

## 🔧 Installation

### Manual Installation

1. Download the plugin files
2. Upload the `ninja-api` folder to `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Navigate to 'API Explorer' in the WordPress admin menu

### From WordPress Admin

1. Go to Plugins → Add New
2. Search for "Ninja API Explorer"
3. Click "Install Now" and then "Activate"
4. Access the plugin from the admin menu

## 🎯 Usage

### Accessing the Plugin

After activation, you'll find "API Explorer" in your WordPress admin menu. The plugin provides three main tabs:

1. **All Routes**: View all registered API routes with filtering options
2. **Grouped Routes**: Routes organized by namespace for better navigation
3. **API Tester**: Built-in tool for testing API endpoints

### Exploring Routes

- **Browse Routes**: Navigate through all available WordPress REST API routes
- **Filter Options**: Use the filter bar to narrow down results by namespace, HTTP method, or public status
- **Search**: Type in the search box to find specific routes
- **Route Details**: Click "Details" to see comprehensive information about any route

### Testing APIs

1. Navigate to the "API Tester" tab
2. Enter the endpoint URL
3. Select the HTTP method (GET, POST, PUT, PATCH, DELETE, etc.)
4. Add request body if needed (for POST/PUT/PATCH requests)
5. Click "Test Endpoint" to execute the request
6. View the response with status code, response time, and body

### Advanced Features

- **Copy URLs**: Click "Copy URL" to copy endpoint URLs for external testing
- **Namespace Grouping**: View routes organized by WordPress namespaces (wp/v2, etc.)
- **Request Logging**: All test requests are logged for future reference
- **OpenAPI Export**: Generate OpenAPI specification for external documentation tools

## 🏗️ Architecture

### MVC Structure

```
App/
├── Controllers/
│   ├── BaseController.php      # Base controller class
│   ├── AdminController.php     # Admin panel controller
│   └── ApiTestController.php   # API testing controller
├── Models/
│   ├── ApiRouteModel.php       # Route data model
│   └── ApiEndpointModel.php    # Endpoint data model
├── Services/
│   └── ApiService.php          # Core API service
├── Helpers/
│   ├── RouteHelper.php         # Route utility functions
│   └── ViewHelper.php          # View rendering helper
└── Views/
    └── admin/
        ├── main-page.php       # Main interface
        ├── settings-page.php   # Settings page
        └── documentation-page.php # Documentation
```

### Key Components

- **ApiService**: Handles WordPress REST API interaction and route discovery
- **ViewHelper**: Manages view rendering and template system
- **RouteHelper**: Provides utility functions for route processing
- **AdminController**: Manages admin interface and user interactions
- **ApiTestController**: Handles API testing functionality

## 🎨 Customization

### Styling

The plugin uses clean CSS that can be easily customized:

- Main styles: `assets/css/admin.css`
- Responsive design included
- WordPress admin theme integration
- Customizable color scheme

### Extending Functionality

The plugin is designed to be extensible:

1. **Custom Controllers**: Extend `BaseController` for new functionality
2. **Additional Services**: Add new service classes for extended features
3. **Custom Views**: Create new view templates for additional pages
4. **Hook Integration**: Use WordPress hooks for custom integrations

## 🔒 Security

- **Nonce Verification**: All AJAX requests use WordPress nonces
- **Permission Checks**: Administrator-level access required
- **Input Sanitization**: All user inputs are properly sanitized
- **Output Escaping**: All outputs are properly escaped
- **Direct Access Protection**: All files include access protection

## 🐛 Troubleshooting

### Common Issues

**Plugin not showing in admin menu:**
- Ensure you have administrator privileges
- Check if the plugin is properly activated
- Verify WordPress version compatibility

**Routes not displaying:**
- Check if WordPress REST API is enabled
- Verify that routes are properly registered
- Check browser console for JavaScript errors

**API tests failing:**
- Verify the endpoint URL is correct
- Check if the endpoint requires authentication
- Ensure the request method matches the endpoint requirements

### Debug Mode

Enable WordPress debug mode to see detailed error messages:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 🤝 Contributing

We welcome contributions from the community! Here's how you can help:

### Ways to Contribute

1. **Bug Reports**: Report issues with detailed information
2. **Feature Requests**: Suggest new features or improvements
3. **Code Contributions**: Submit pull requests with fixes or enhancements
4. **Documentation**: Help improve documentation and examples
5. **Testing**: Test the plugin on different WordPress setups

### Development Setup

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature-name`
3. Make your changes following the existing code style
4. Test your changes thoroughly
5. Submit a pull request with a clear description

### Code Standards

- Follow WordPress Coding Standards
- Use PascalCase for class names
- Maintain MVC architecture
- Include proper documentation
- Write clean, readable code

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- WordPress REST API team for the excellent API framework
- WordPress community for inspiration and feedback
- Contributors who help improve this plugin

## 📞 Support

- **Documentation**: Check this README and inline code comments
- **Issues**: Report bugs and request features via GitHub issues
- **Community**: Join WordPress developer communities for general help

## 🔄 Changelog

### Version 1.0.0
- Initial release
- Complete MVC architecture implementation
- Route discovery and display functionality
- Built-in API testing tool
- Tabbed interface with filtering and search
- Responsive design for all devices
- OpenAPI specification generation
- Request logging system

---

**Made with ❤️ for the WordPress community**