import React, {Component} from 'react';
// @material-ui/core components
import withStyles from '@material-ui/core/styles/withStyles';
import InputAdornment from '@material-ui/core/InputAdornment';

import {connect} from 'react-redux';
import {bindActionCreators} from 'redux';
import {enqueueSnackbar} from 'app/store/actions/snackbarActions';

// @material-ui/icons
import {Lock, Face} from '@material-ui/icons';

// core components
import SecurityLayout from 'app/layouts/SecurityLayout/SecurityLayout.jsx';
import Button from 'components/CustomButtons/Button.jsx';
import CardBody from 'components/Card/CardBody.jsx';
import CardHeader from 'components/Card/CardHeader.jsx';
import CardFooter from 'components/Card/CardFooter.jsx';
import CustomInput from 'components/CustomInput/CustomInput.jsx';

import loginPageStyle from 'assets/jss/material-kit-react/views/loginPage.jsx';

import SecurityKey from 'app/img/securitykey.min.svg';
import {authSuccess} from 'app/store/actions/authenticationActions';
import {useRegistration} from '@web-auth/webauthn-helper';

class RegisterPage extends Component {
  state = {
      isFormValid: false,
      username: '',
      displayName: '',
      isDeviceInteractionEnabled: false,
  };

  handleKeyPressed = event => {
    if (event.which === 13) {
      this.handleFormValidation(event)
    }
  };

  handleUsernameChanged = event => {
      this.setState({
          username: event.target.value,
          isFormValid: event.target.value !== '' && this.state.displayName !== '',
      });
  };

  handleDisplayNameChanged = event => {
      this.setState({
          displayName: event.target.value,
          isFormValid: this.state.username !== '' && event.target.value !== '',
      });
  };

  registrationFailureHandler = () => {
      this.props.enqueueSnackbar({
          message:
        'An error occurred during the registration process. Please try again later.',
      });
      this.setState({
          isDeviceInteractionEnabled: false,
      });
  };

  registrationSuccessHandler = json => {
      if (json.status !== undefined && json.status === 'ok') {
          this.props.authSuccess(json)
          this.props.enqueueSnackbar({
              message: 'Your account have been successfully created!',
          });
          this.setState({
              isDeviceInteractionEnabled: false,
          });
          this.props.history.push('/');
      } else {
          this.registrationFailureHandler(json.errorMessage);
      }
  };

  handleRegistrationProcess = useRegistration({
    actionUrl: '/api/register',
    optionsUrl: '/api/register/options',
  });

  handleFormValidation = () => {
    this.handleRegistrationProcess({
        username: this.state.username,
        displayName: this.state.displayName,
      })
      .then(json => this.registrationSuccessHandler(json))
      .catch(err => this.registrationFailureHandler(err));
  };

  render() {
      const {classes} = this.props;

      let cardBody = (
          <form className={classes.form}>
              <CardHeader color="primary" className={classes.cardHeader}>
                  <h4>Create an account</h4>
              </CardHeader>
              <CardBody>
                  <p>
            Want to see how Webauthn will make you a better life? Just create an
            account by filling the form below.
                  </p>
                  <CustomInput
                      labelText="Username"
                      id="username"
                      formControlProps={{
                          fullWidth: true,
                      }}
                      inputProps={{
                          onKeyPress: event => this.handleKeyPressed(event),
                          onChange: event => this.handleUsernameChanged(event),
                          type: 'text',
                          value: this.state.username,
                          endAdornment: (
                              <InputAdornment position="end">
                                  <Lock className={classes.inputIconsColor} />
                              </InputAdornment>
                          ),
                      }}
                  />
                  <CustomInput
                      labelText="Display name"
                      id="display_name"
                      formControlProps={{
                          fullWidth: true,
                      }}
                      inputProps={{
                          onKeyPress: event => this.handleKeyPressed(event),
                          onChange: event => this.handleDisplayNameChanged(event),
                          type: 'text',
                          value: this.state.displayName,
                          endAdornment: (
                              <InputAdornment position="end">
                                  <Face className={classes.inputIconsColor} />
                              </InputAdornment>
                          ),
                      }}
                  />
                  <i>
            This is just a demo application, we don’t analyze or sell the
            information you will provide. Everything is deleted each month.
                  </i>
              </CardBody>
              <CardFooter className={classes.cardFooter}>
                  <Button
                      simple
                      color="primary"
                      size="lg"
                      disabled={!this.state.isFormValid}
                      onKeyPress={this.handleKeyPressed}
                      onClick={this.handleFormValidation}
                  >
            Get started
                  </Button>
              </CardFooter>
          </form>
      );
      if (this.state.isDeviceInteractionEnabled) {
          cardBody = (
              <div>
                  <CardHeader color="primary" className={classes.cardHeader}>
                      <h4>Create an account</h4>
                  </CardHeader>
                  <CardBody>
                      <p>
              You should now be notified to tap your security device (button,
              bluetooth, NFC, fingerprint…).
                      </p>
                      <img src={SecurityKey} alt="Tap your device" width="100%" />
                  </CardBody>
              </div>
          );
      }

      return (
          <SecurityLayout>
              {cardBody}
          </SecurityLayout>
      );
  }
}

const mapDispatchToProps = dispatch => {
  return bindActionCreators({
    enqueueSnackbar,
    authSuccess,
  }, dispatch);
};

export default connect(null, mapDispatchToProps)(withStyles(loginPageStyle)(RegisterPage));
