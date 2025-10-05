# Contributing to Ninja API Explorer

Thank you for your interest in contributing to Ninja API Explorer! This document provides guidelines and information for contributors.

## 🤝 How to Contribute

### Reporting Bugs

When reporting bugs, please include:

1. **WordPress Version**: Your WordPress version
2. **PHP Version**: Your PHP version
3. **Plugin Version**: The version of Ninja API Explorer you're using
4. **Steps to Reproduce**: Clear steps to reproduce the issue
5. **Expected Behavior**: What you expected to happen
6. **Actual Behavior**: What actually happened
7. **Error Messages**: Any error messages or console logs
8. **Screenshots**: If applicable, include screenshots

### Suggesting Features

For feature requests, please provide:

1. **Use Case**: Describe the problem you're trying to solve
2. **Proposed Solution**: How you think this should be implemented
3. **Alternatives**: Any alternative solutions you've considered
4. **Additional Context**: Any other relevant information

### Code Contributions

#### Development Setup

1. **Fork the Repository**: Create your own fork of the project
2. **Clone Locally**: Clone your fork to your local machine
3. **Create Branch**: Create a new branch for your feature/fix
4. **Make Changes**: Implement your changes following the coding standards
5. **Test Thoroughly**: Test your changes on different WordPress setups
6. **Submit Pull Request**: Create a pull request with a clear description

#### Branch Naming

Use descriptive branch names:
- `feature/your-feature-name` for new features
- `bugfix/issue-description` for bug fixes
- `enhancement/improvement-description` for enhancements
- `docs/documentation-update` for documentation changes

## 📋 Coding Standards

### PHP Standards

- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
- Use PascalCase for class names
- Use camelCase for method and variable names
- Use UPPER_CASE for constants
- Include proper PHPDoc comments for all classes and methods

### JavaScript Standards

- Follow modern JavaScript ES6+ standards
- Use meaningful variable and function names
- Include JSDoc comments for complex functions
- Use jQuery for DOM manipulation (WordPress standard)
- Ensure cross-browser compatibility

### CSS Standards

- Use meaningful class names with BEM methodology when appropriate
- Keep selectors specific but not overly complex
- Use consistent indentation (2 spaces)
- Comment complex CSS rules
- Ensure responsive design principles

### Architecture Guidelines

- Maintain MVC architecture
- Keep controllers thin
- Use services for business logic
- Keep views simple and focused on presentation
- Follow single responsibility principle

## 🧪 Testing

### Testing Checklist

Before submitting a pull request, ensure:

- [ ] Code works on WordPress 5.0+
- [ ] Code works with PHP 7.4+
- [ ] No PHP errors or warnings
- [ ] No JavaScript console errors
- [ ] Responsive design works on mobile devices
- [ ] All existing functionality still works
- [ ] New features are properly documented

### Test Scenarios

1. **Basic Functionality**: All core features work as expected
2. **Edge Cases**: Handle empty data, invalid inputs, network errors
3. **Performance**: No significant performance degradation
4. **Security**: No security vulnerabilities introduced
5. **Compatibility**: Works with popular WordPress themes and plugins

## 📝 Documentation

### Code Documentation

- Include PHPDoc comments for all public methods
- Document complex algorithms or business logic
- Include examples for public APIs
- Update README.md for new features

### User Documentation

- Update README.md with new features
- Include usage examples
- Document any new configuration options
- Provide troubleshooting information

## 🔄 Pull Request Process

### Before Submitting

1. **Self-Review**: Review your own code for errors and improvements
2. **Test Changes**: Thoroughly test your changes
3. **Update Documentation**: Update relevant documentation
4. **Check Standards**: Ensure code follows project standards

### Pull Request Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Tested on WordPress 5.0+
- [ ] Tested with PHP 7.4+
- [ ] No console errors
- [ ] Responsive design verified

## Checklist
- [ ] Code follows project standards
- [ ] Self-review completed
- [ ] Documentation updated
- [ ] No breaking changes (or clearly documented)
```

## 🏷️ Release Process

### Version Numbering

We follow semantic versioning (MAJOR.MINOR.PATCH):

- **MAJOR**: Breaking changes
- **MINOR**: New features, backward compatible
- **PATCH**: Bug fixes, backward compatible

### Release Checklist

- [ ] All tests pass
- [ ] Documentation updated
- [ ] Changelog updated
- [ ] Version numbers updated
- [ ] Release notes prepared

## 💬 Communication

### Getting Help

- **GitHub Issues**: For bugs and feature requests
- **Discussions**: For general questions and ideas
- **Code Review**: For pull request feedback

### Community Guidelines

- Be respectful and constructive
- Help others learn and grow
- Follow WordPress community guidelines
- Maintain a positive, welcoming environment

## 🎯 Areas for Contribution

### High Priority

- **Bug Fixes**: Fixing reported issues
- **Performance Improvements**: Optimizing existing code
- **Accessibility**: Improving accessibility features
- **Mobile Optimization**: Enhancing mobile experience

### Medium Priority

- **New Features**: Adding requested functionality
- **Documentation**: Improving documentation quality
- **Testing**: Adding automated tests
- **Code Quality**: Refactoring and improving code

### Low Priority

- **UI/UX Improvements**: Enhancing user interface
- **Internationalization**: Adding translation support
- **Integration**: Adding third-party integrations
- **Advanced Features**: Complex feature additions

## 🙏 Recognition

Contributors will be recognized in:

- README.md contributors section
- Release notes
- Plugin credits
- GitHub contributors list

## 📞 Contact

If you have questions about contributing:

- Open a GitHub issue
- Start a discussion
- Check existing documentation

---

Thank you for contributing to Ninja API Explorer! Your contributions help make this plugin better for everyone in the WordPress community.
