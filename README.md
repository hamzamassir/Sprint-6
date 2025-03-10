# Custom Drupal Modules Collection

This repository contains three custom Drupal modules designed to demonstrate different aspects of Drupal module development.

## Modules Overview

### 1. Hello Module
A simple module that creates a page displaying "Hello World".

- **Version Compatibility**: Drupal 8, 9, 10, 11
- **Package**: CustomModules
- **Features**:
  - Creates a basic page route
  - Demonstrates simple controller implementation
  - Shows basic Drupal routing system

### 2. Movie Directory Module
A module for managing and displaying movie information.

- **Version Compatibility**: Drupal 8, 9, 10, 11
- **Package**: CustomModules
- **Features**:
  - Movie search functionality
  - Movie details display
  - Custom content type for movies

### 3. Hello Block Module
A module that implements a custom Drupal block.

- **Version Compatibility**: Drupal 8, 9, 10, 11
- **Package**: CustomModules
- **Features**:
  - Custom block plugin
  - Configurable block settings
  - Demonstrates block API usage

## Installation

1. Clone this repository to your Drupal installation's `modules/custom` directory:
```bash
git clone [repository-url] modules/custom
```

2. Enable the modules through Drupal's admin interface:
   - Navigate to `Admin > Extend`
   - Find the modules under the "CustomModules" package
   - Check the boxes next to the modules you want to enable
   - Click "Install"

## Usage

### Hello Module
- Visit `/hello` to see the "Hello World" message

### Movie Directory
- Access the movie directory at `/movies`
- Use the search functionality to find movies
- Click on individual movies to view details

### Hello Block
- Go to `Admin > Structure > Block Layout`
- Place the Hello block in your desired region
- Configure block settings as needed

## Requirements

- Drupal 8, 9, 10, or 11
- PHP 7.4 or higher

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## Authors

- Massir Hamza

## Support

For issues, questions, or contributions, please create an issue in the repository or contact the maintainers.

